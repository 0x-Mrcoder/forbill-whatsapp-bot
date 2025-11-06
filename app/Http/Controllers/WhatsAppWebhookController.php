<?php

namespace App\Http\Controllers;

use App\Services\WhatsAppService;
use App\Services\MessageProcessingService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    private WhatsAppService $whatsAppService;
    private MessageProcessingService $messageProcessingService;

    public function __construct(
        WhatsAppService $whatsAppService,
        MessageProcessingService $messageProcessingService
    ) {
        $this->whatsAppService = $whatsAppService;
        $this->messageProcessingService = $messageProcessingService;
    }

    /**
     * Handle webhook verification (GET request)
     */
    public function verify(Request $request): Response
    {
        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        Log::info('WhatsApp webhook verification attempt', [
            'mode' => $mode,
            'token' => $token,
            'challenge' => $challenge
        ]);

        if ($mode === 'subscribe' && $this->whatsAppService->verifyWebhookToken($token)) {
            Log::info('WhatsApp webhook verified successfully');
            return response($challenge, 200);
        }

        Log::warning('WhatsApp webhook verification failed');
        return response('Forbidden', 403);
    }

    /**
     * Handle incoming webhooks (POST request)
     */
    public function handle(Request $request): Response
    {
        try {
            $payload = $request->getContent();
            $signature = $request->header('X-Hub-Signature-256');

            Log::info('WhatsApp webhook received', [
                'signature' => $signature,
                'payload_size' => strlen($payload)
            ]);

            // Validate signature if app secret is configured
            if (config('services.whatsapp.app_secret') && !$this->whatsAppService->validateWebhookSignature($payload, $signature)) {
                Log::warning('WhatsApp webhook signature validation failed');
                return response('Unauthorized', 401);
            }

            $data = json_decode($payload, true);

            if (!$data) {
                Log::error('Invalid JSON payload received');
                return response('Bad Request', 400);
            }

            $this->processWebhookData($data);

            return response('OK', 200);

        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response('Internal Server Error', 500);
        }
    }

    /**
     * Process webhook data
     */
    private function processWebhookData(array $data): void
    {
        if (!isset($data['entry'])) {
            Log::debug('No entry data in webhook');
            return;
        }

        foreach ($data['entry'] as $entry) {
            if (!isset($entry['changes'])) {
                continue;
            }

            foreach ($entry['changes'] as $change) {
                if ($change['field'] !== 'messages') {
                    continue;
                }

                $this->processMessageChanges($change['value']);
            }
        }
    }

    /**
     * Process message changes
     */
    private function processMessageChanges(array $value): void
    {
        // Process incoming messages
        if (isset($value['messages'])) {
            foreach ($value['messages'] as $message) {
                $this->processIncomingMessage($message);
            }
        }

        // Process message statuses (delivered, read, etc.)
        if (isset($value['statuses'])) {
            foreach ($value['statuses'] as $status) {
                $this->processMessageStatus($status);
            }
        }
    }

    /**
     * Process incoming message
     */
    private function processIncomingMessage(array $message): void
    {
        // Only process text messages for now
        if ($message['type'] !== 'text') {
            Log::debug('Ignoring non-text message', ['type' => $message['type']]);
            return;
        }

        Log::info('Processing incoming text message', [
            'from' => $message['from'],
            'id' => $message['id'],
            'text' => $message['text']['body'] ?? ''
        ]);

        $this->messageProcessingService->processMessage($message);
    }

    /**
     * Process message status updates
     */
    private function processMessageStatus(array $status): void
    {
        Log::debug('Message status update', [
            'id' => $status['id'],
            'status' => $status['status'],
            'timestamp' => $status['timestamp'] ?? null
        ]);

        // Handle status updates (delivered, read, failed, etc.)
        // This can be used for tracking message delivery
    }

    /**
     * Test endpoint to send a message
     */
    public function test(Request $request): Response
    {
        try {
            $to = $request->input('to');
            $message = $request->input('message', 'Hello from ForBill! 🎉');

            if (!$to) {
                return response()->json(['error' => 'Phone number required'], 400);
            }

            $result = $this->whatsAppService->sendTextMessage($to, $message);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error in test message', [
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
