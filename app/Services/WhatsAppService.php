<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private Client $client;
    private string $token;
    private string $phoneNumberId;
    private string $apiVersion;
    private string $baseUrl;

    public function __construct()
    {
        $this->client = new Client();
        $this->token = config('services.whatsapp.token');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id');
        $this->apiVersion = config('services.whatsapp.api_version', 'v22.0');
        $this->baseUrl = "https://graph.facebook.com/{$this->apiVersion}";
    }

    /**
     * Send a text message to a WhatsApp number
     */
    public function sendTextMessage(string $to, string $message): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'text',
            'text' => [
                'body' => $message
            ]
        ];

        return $this->sendMessage($payload);
    }

    /**
     * Send a template message
     */
    public function sendTemplateMessage(string $to, string $templateName, array $parameters = [], string $languageCode = 'en_US'): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode
                ]
            ]
        ];

        if (!empty($parameters)) {
            $payload['template']['components'] = [
                [
                    'type' => 'body',
                    'parameters' => $parameters
                ]
            ];
        }

        return $this->sendMessage($payload);
    }

    /**
     * Send an interactive button message
     */
    public function sendButtonMessage(string $to, string $bodyText, array $buttons): array
    {
        $interactiveButtons = [];
        foreach ($buttons as $index => $button) {
            $interactiveButtons[] = [
                'type' => 'reply',
                'reply' => [
                    'id' => $button['id'] ?? "btn_{$index}",
                    'title' => $button['title']
                ]
            ];
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'button',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'buttons' => $interactiveButtons
                ]
            ]
        ];

        return $this->sendMessage($payload);
    }

    /**
     * Send a list message
     */
    public function sendListMessage(string $to, string $bodyText, string $buttonText, array $sections): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'to' => $this->formatPhoneNumber($to),
            'type' => 'interactive',
            'interactive' => [
                'type' => 'list',
                'body' => [
                    'text' => $bodyText
                ],
                'action' => [
                    'button' => $buttonText,
                    'sections' => $sections
                ]
            ]
        ];

        return $this->sendMessage($payload);
    }

    /**
     * Mark message as read
     */
    public function markMessageAsRead(string $messageId): array
    {
        $payload = [
            'messaging_product' => 'whatsapp',
            'status' => 'read',
            'message_id' => $messageId
        ];

        return $this->sendMessage($payload);
    }

    /**
     * Send message to WhatsApp API
     */
    private function sendMessage(array $payload): array
    {
        try {
            $response = $this->client->post("{$this->baseUrl}/{$this->phoneNumberId}/messages", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}",
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);
            
            Log::info('WhatsApp message sent successfully', [
                'payload' => $payload,
                'response' => $responseData
            ]);

            return [
                'success' => true,
                'data' => $responseData
            ];

        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() 
                ? json_decode($e->getResponse()->getBody()->getContents(), true)
                : ['error' => $e->getMessage()];

            Log::error('WhatsApp message failed', [
                'payload' => $payload,
                'error' => $errorResponse
            ]);

            return [
                'success' => false,
                'error' => $errorResponse
            ];
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-numeric characters
        $phone = preg_replace('/\D/', '', $phone);

        // If it starts with 0, replace with 234 (Nigeria)
        if (substr($phone, 0, 1) === '0') {
            $phone = '234' . substr($phone, 1);
        }

        // If it doesn't start with 234, assume it's Nigerian and add 234
        if (substr($phone, 0, 3) !== '234') {
            $phone = '234' . $phone;
        }

        return $phone;
    }

    /**
     * Validate webhook signature
     */
    public function validateWebhookSignature(string $payload, string $signature): bool
    {
        $appSecret = config('services.whatsapp.app_secret');
        if (!$appSecret) {
            return true; // Skip validation if no app secret is configured
        }

        $expectedSignature = hash_hmac('sha256', $payload, $appSecret);
        return hash_equals("sha256={$expectedSignature}", $signature);
    }

    /**
     * Verify webhook token
     */
    public function verifyWebhookToken(string $token): bool
    {
        return $token === config('services.whatsapp.webhook_verify_token');
    }
}