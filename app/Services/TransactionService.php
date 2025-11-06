<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\VtuProvider;
use App\Models\ConversationSession;
use Illuminate\Support\Str;

class TransactionService
{
    private VtuService $vtuService;
    private WhatsAppService $whatsAppService;

    public function __construct(VtuService $vtuService, WhatsAppService $whatsAppService)
    {
        $this->vtuService = $vtuService;
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Create a new transaction
     */
    public function createTransaction(
        User $user,
        string $serviceType,
        string $networkCode,
        string $recipientPhone,
        float $amount
    ): Transaction {
        $provider = VtuProvider::getByNetworkCode($networkCode);
        
        if (!$provider) {
            throw new \Exception("Network provider not found for {$networkCode}");
        }

        $commission = $provider->calculateCommission($amount);
        $providerAmount = $amount - $commission;

        return Transaction::create([
            'reference' => $this->generateReference(),
            'user_id' => $user->id,
            'vtu_provider_id' => $provider->id,
            'recipient_phone' => $recipientPhone,
            'service_type' => $serviceType,
            'network_code' => $networkCode,
            'amount' => $amount,
            'commission' => $commission,
            'provider_amount' => $providerAmount,
            'status' => 'pending',
            'payment_method' => 'wallet', // Default to wallet for now
        ]);
    }

    /**
     * Process transaction (purchase airtime/data)
     */
    public function processTransaction(Transaction $transaction): bool
    {
        try {
            $transaction->markAsProcessing();

            // Check if user has sufficient wallet balance
            if (!$transaction->user->hasSufficientBalance($transaction->amount)) {
                $transaction->markAsFailed('Insufficient wallet balance');
                return false;
            }

            // Debit user wallet
            $transaction->user->debitWallet($transaction->amount);

            // Make VTU purchase
            if ($transaction->service_type === 'airtime') {
                $result = $this->vtuService->purchaseAirtime($transaction);
            } elseif ($transaction->service_type === 'data') {
                // For data, we'd need plan code - for now, skip
                $result = ['success' => false, 'error' => ['message' => 'Data purchase not implemented yet']];
            } else {
                $result = ['success' => false, 'error' => ['message' => 'Unsupported service type']];
            }

            if ($result['success']) {
                $providerReference = $result['data']['reference'] ?? null;
                $transaction->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'provider_reference' => $providerReference,
                    'provider_response' => json_encode($result['provider_response']),
                ]);

                $this->sendSuccessNotification($transaction);
                return true;
            } else {
                // Refund wallet on failure
                $transaction->user->creditWallet($transaction->amount);
                $transaction->markAsFailed($result['error']['message'] ?? 'VTU purchase failed');
                
                $this->sendFailureNotification($transaction);
                return false;
            }

        } catch (\Exception $e) {
            // Refund wallet on exception
            if ($transaction->status === 'processing') {
                $transaction->user->creditWallet($transaction->amount);
            }
            
            $transaction->markAsFailed($e->getMessage());
            $this->sendFailureNotification($transaction);
            return false;
        }
    }

    /**
     * Send success notification via WhatsApp
     */
    private function sendSuccessNotification(Transaction $transaction): void
    {
        $message = "✅ *Transaction Successful!*\n\n";
        $message .= "₦{$transaction->amount} {$this->vtuService->getNetworkName($transaction->network_code)} ";
        $message .= "{$transaction->service_type} sent to {$transaction->recipient_phone}\n\n";
        $message .= "Reference: {$transaction->reference}\n";
        $message .= "Time: " . $transaction->completed_at->format('M j, Y g:i A');

        $this->whatsAppService->sendTextMessage(
            $transaction->user->phone,
            $message
        );
    }

    /**
     * Send failure notification via WhatsApp
     */
    private function sendFailureNotification(Transaction $transaction): void
    {
        $message = "❌ *Transaction Failed*\n\n";
        $message .= "We couldn't complete your ₦{$transaction->amount} ";
        $message .= "{$this->vtuService->getNetworkName($transaction->network_code)} ";
        $message .= "{$transaction->service_type} purchase.\n\n";
        $message .= "Your wallet has been refunded.\n";
        $message .= "Reference: {$transaction->reference}\n\n";
        $message .= "Reason: {$transaction->failure_reason}";

        $this->whatsAppService->sendTextMessage(
            $transaction->user->phone,
            $message
        );
    }

    /**
     * Get user's recent transactions
     */
    public function getUserTransactions(User $user, int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $user->transactions()
            ->with(['vtuProvider'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Generate unique transaction reference
     */
    private function generateReference(): string
    {
        do {
            $reference = 'TXN_' . strtoupper(Str::random(12));
        } while (Transaction::where('reference', $reference)->exists());

        return $reference;
    }

    /**
     * Get transaction status message for user
     */
    public function getTransactionStatusMessage(Transaction $transaction): string
    {
        $networkName = $this->vtuService->getNetworkName($transaction->network_code);
        
        switch ($transaction->status) {
            case 'pending':
                return "⏳ Your ₦{$transaction->amount} {$networkName} {$transaction->service_type} purchase is pending.\n\nRef: {$transaction->reference}";
            
            case 'processing':
                return "🔄 Your ₦{$transaction->amount} {$networkName} {$transaction->service_type} purchase is being processed.\n\nRef: {$transaction->reference}";
            
            case 'completed':
                return "✅ Your ₦{$transaction->amount} {$networkName} {$transaction->service_type} purchase was successful!\n\nSent to: {$transaction->recipient_phone}\nRef: {$transaction->reference}\nTime: " . $transaction->completed_at->format('M j, Y g:i A');
            
            case 'failed':
                return "❌ Your ₦{$transaction->amount} {$networkName} {$transaction->service_type} purchase failed.\n\nReason: {$transaction->failure_reason}\nRef: {$transaction->reference}";
            
            case 'refunded':
                return "🔄 Your ₦{$transaction->amount} {$networkName} {$transaction->service_type} purchase was refunded.\n\nRef: {$transaction->reference}";
            
            default:
                return "❓ Transaction status unknown.\n\nRef: {$transaction->reference}";
        }
    }
}