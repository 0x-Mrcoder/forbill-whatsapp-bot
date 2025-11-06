<?php

namespace App\Services;

use App\Models\User;
use App\Models\ConversationSession;
use Illuminate\Support\Facades\Log;

class MessageProcessingService
{
    private WhatsAppService $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Process incoming WhatsApp message
     */
    public function processMessage(array $messageData): void
    {
        try {
            $from = $messageData['from'];
            $messageId = $messageData['id'];
            $text = $messageData['text']['body'] ?? '';

            Log::info('Processing WhatsApp message', [
                'from' => $from,
                'message_id' => $messageId,
                'text' => $text
            ]);

            // Mark message as read
            $this->whatsAppService->markMessageAsRead($messageId);

            // Get or create user
            $user = $this->getOrCreateUser($from);

            // Process the message
            $this->handleUserMessage($user, $text);

        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle user message with simple rule-based responses
     */
    private function handleUserMessage(User $user, string $message): void
    {
        $message = strtolower(trim($message));
        $response = '';

        // Simple greeting responses
        if (in_array($message, ['hi', 'hello', 'hey', 'start'])) {
            $response = $this->getWelcomeMessage($user);
        }
        // Help command
        elseif (in_array($message, ['help', 'menu', 'options'])) {
            $response = $this->getHelpMessage();
        }
        // Balance inquiry
        elseif (in_array($message, ['balance', 'wallet', 'my balance'])) {
            $response = $this->getBalanceMessage($user);
        }
        // Airtime purchase intent (basic detection)
        elseif (str_contains($message, 'airtime') || str_contains($message, 'recharge')) {
            $response = $this->getAirtimeInstructions();
        }
        // Data purchase intent
        elseif (str_contains($message, 'data') || str_contains($message, 'internet')) {
            $response = $this->getDataInstructions();
        }
        // Transaction status
        elseif (str_contains($message, 'status') || str_contains($message, 'transaction')) {
            $response = $this->getTransactionStatusInstructions();
        }
        // Default response
        else {
            $response = $this->getDefaultResponse();
        }

        // Send response
        if ($response) {
            $this->whatsAppService->sendTextMessage($user->phone, $response);
        }
    }

    /**
     * Get or create user from phone number
     */
    private function getOrCreateUser(string $phone): User
    {
        $formattedPhone = $this->formatPhone($phone);
        
        return User::firstOrCreate(
            ['phone' => $formattedPhone],
            [
                'name' => 'ForBill User',
                'wallet_balance' => 0,
                'is_active' => true,
                'last_seen_at' => now(),
            ]
        );
    }

    /**
     * Format phone number
     */
    private function formatPhone(string $phone): string
    {
        // Remove + and any non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);
        
        // Ensure it starts with 234
        if (substr($phone, 0, 3) !== '234') {
            if (substr($phone, 0, 1) === '0') {
                $phone = '234' . substr($phone, 1);
            } else {
                $phone = '234' . $phone;
            }
        }
        
        return $phone;
    }

    /**
     * Welcome message
     */
    private function getWelcomeMessage(User $user): string
    {
        return "🎉 *Welcome to ForBill!*\n\n" .
               "Your one-stop platform for:\n" .
               "📱 Airtime Top-up\n" .
               "📊 Data Bundles\n" .
               "⚡ Electricity Bills\n" .
               "📺 TV Subscriptions\n\n" .
               "💰 Wallet Balance: ₦" . number_format($user->wallet_balance, 2) . "\n\n" .
               "Type *help* to see available commands.";
    }

    /**
     * Help message
     */
    private function getHelpMessage(): string
    {
        return "📋 *ForBill Commands*\n\n" .
               "💰 *balance* - Check wallet balance\n" .
               "📱 *airtime* - Buy airtime\n" .
               "📊 *data* - Buy data bundles\n" .
               "📋 *status* - Check transaction status\n" .
               "❓ *help* - Show this menu\n\n" .
               "_More services coming soon!_";
    }

    /**
     * Balance message
     */
    private function getBalanceMessage(User $user): string
    {
        return "💰 *Your Wallet Balance*\n\n" .
               "₦" . number_format($user->wallet_balance, 2) . "\n\n" .
               "_Contact support to fund your wallet._";
    }

    /**
     * Airtime instructions
     */
    private function getAirtimeInstructions(): string
    {
        return "📱 *Buy Airtime*\n\n" .
               "To purchase airtime, send a message like:\n" .
               "_\"Buy ₦500 MTN airtime for 08012345678\"_\n\n" .
               "💡 *Supported Networks:*\n" .
               "• MTN\n" .
               "• Airtel\n" .
               "• GLO\n" .
               "• 9Mobile\n\n" .
               "_Amount range: ₦50 - ₦50,000_";
    }

    /**
     * Data instructions
     */
    private function getDataInstructions(): string
    {
        return "📊 *Buy Data Bundles*\n\n" .
               "To purchase data, send a message like:\n" .
               "_\"Buy 1GB MTN data for 08012345678\"_\n\n" .
               "💡 *Available Plans:*\n" .
               "• 1GB - ₦1,000\n" .
               "• 2GB - ₦2,000\n" .
               "• 5GB - ₦2,500\n" .
               "• 10GB - ₦5,000\n\n" .
               "_More plans available for all networks_";
    }

    /**
     * Transaction status instructions
     */
    private function getTransactionStatusInstructions(): string
    {
        return "📋 *Check Transaction Status*\n\n" .
               "To check your transaction status:\n" .
               "_\"Status TXN_ABC123456789\"_\n\n" .
               "Or check your recent transactions:\n" .
               "_\"My transactions\"_";
    }

    /**
     * Default response
     */
    private function getDefaultResponse(): string
    {
        return "🤖 I didn't understand that command.\n\n" .
               "Type *help* to see available options or try:\n" .
               "• _\"Buy ₦500 MTN airtime for 08012345678\"_\n" .
               "• _\"Buy 1GB Airtel data for 08012345678\"_\n" .
               "• _\"Balance\"_\n" .
               "• _\"Help\"_";
    }
}