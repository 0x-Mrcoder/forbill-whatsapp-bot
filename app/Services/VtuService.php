<?php

namespace App\Services;

use App\Models\VtuProvider;
use App\Models\Transaction;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class VtuService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 30,
            'verify' => false, // For development, should be true in production
        ]);
    }

    /**
     * Purchase airtime
     */
    public function purchaseAirtime(Transaction $transaction): array
    {
        $provider = $transaction->vtuProvider;
        
        if (!$provider) {
            return $this->errorResponse('VTU provider not found');
        }

        $payload = [
            'network' => $transaction->network_code,
            'phone' => $transaction->recipient_phone,
            'amount' => $transaction->provider_amount,
            'reference' => $transaction->reference,
        ];

        return $this->makeProviderRequest($provider, 'airtime', $payload);
    }

    /**
     * Purchase data bundle
     */
    public function purchaseData(Transaction $transaction, string $planCode): array
    {
        $provider = $transaction->vtuProvider;
        
        if (!$provider) {
            return $this->errorResponse('VTU provider not found');
        }

        $payload = [
            'network' => $transaction->network_code,
            'phone' => $transaction->recipient_phone,
            'plan_code' => $planCode,
            'reference' => $transaction->reference,
        ];

        return $this->makeProviderRequest($provider, 'data', $payload);
    }

    /**
     * Get data plans for a network
     */
    public function getDataPlans(string $networkCode): array
    {
        $provider = VtuProvider::getByNetworkCode($networkCode);
        
        if (!$provider) {
            return $this->errorResponse('Network provider not found');
        }

        // This would typically be a separate endpoint for data plans
        // For now, return mock data
        return $this->getMockDataPlans($networkCode);
    }

    /**
     * Check transaction status
     */
    public function checkTransactionStatus(Transaction $transaction): array
    {
        $provider = $transaction->vtuProvider;
        
        if (!$provider || !$transaction->provider_reference) {
            return $this->errorResponse('Cannot check status - missing provider or reference');
        }

        $payload = [
            'reference' => $transaction->provider_reference,
        ];

        return $this->makeProviderRequest($provider, 'status', $payload);
    }

    /**
     * Make request to VTU provider
     */
    private function makeProviderRequest(VtuProvider $provider, string $action, array $payload): array
    {
        try {
            $endpoint = $this->getEndpoint($provider, $action);
            $headers = $this->getHeaders($provider);
            
            // Add provider authentication to payload
            $payload['api_key'] = $provider->api_key;
            if ($provider->secret_key) {
                $payload['secret_key'] = $provider->secret_key;
            }

            Log::info("Making VTU request", [
                'provider' => $provider->name,
                'action' => $action,
                'endpoint' => $endpoint,
                'payload' => array_except($payload, ['api_key', 'secret_key']) // Don't log sensitive data
            ]);

            $response = $this->client->post($endpoint, [
                'headers' => $headers,
                'json' => $payload,
            ]);

            $responseData = json_decode($response->getBody()->getContents(), true);

            Log::info("VTU request successful", [
                'provider' => $provider->name,
                'action' => $action,
                'response' => $responseData
            ]);

            return [
                'success' => true,
                'data' => $responseData,
                'provider_response' => $responseData
            ];

        } catch (RequestException $e) {
            $errorResponse = $e->hasResponse() 
                ? json_decode($e->getResponse()->getBody()->getContents(), true)
                : ['error' => $e->getMessage()];

            Log::error("VTU request failed", [
                'provider' => $provider->name,
                'action' => $action,
                'error' => $errorResponse,
                'payload' => array_except($payload, ['api_key', 'secret_key'])
            ]);

            return [
                'success' => false,
                'error' => $errorResponse,
                'provider_response' => $errorResponse
            ];
        }
    }

    /**
     * Get endpoint for provider action
     */
    private function getEndpoint(VtuProvider $provider, string $action): string
    {
        $baseUrl = rtrim($provider->api_endpoint, '/');
        
        $endpoints = [
            'airtime' => '/airtime',
            'data' => '/data',
            'status' => '/status',
        ];

        return $baseUrl . ($endpoints[$action] ?? '');
    }

    /**
     * Get headers for provider request
     */
    private function getHeaders(VtuProvider $provider): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'ForBill/1.0',
        ];
    }

    /**
     * Get mock data plans (replace with real provider integration)
     */
    private function getMockDataPlans(string $networkCode): array
    {
        $plans = [
            'mtn' => [
                ['code' => 'mtn_1gb_30', 'name' => '1GB - 30 Days', 'amount' => 1000],
                ['code' => 'mtn_2gb_30', 'name' => '2GB - 30 Days', 'amount' => 2000],
                ['code' => 'mtn_5gb_30', 'name' => '5GB - 30 Days', 'amount' => 2500],
                ['code' => 'mtn_10gb_30', 'name' => '10GB - 30 Days', 'amount' => 5000],
            ],
            'airtel' => [
                ['code' => 'airtel_1gb_30', 'name' => '1GB - 30 Days', 'amount' => 1000],
                ['code' => 'airtel_2gb_30', 'name' => '2GB - 30 Days', 'amount' => 2000],
                ['code' => 'airtel_5gb_30', 'name' => '5GB - 30 Days', 'amount' => 2500],
                ['code' => 'airtel_10gb_30', 'name' => '10GB - 30 Days', 'amount' => 5000],
            ],
            'glo' => [
                ['code' => 'glo_1gb_30', 'name' => '1GB - 30 Days', 'amount' => 1000],
                ['code' => 'glo_2gb_30', 'name' => '2GB - 30 Days', 'amount' => 2000],
                ['code' => 'glo_5gb_30', 'name' => '5GB - 30 Days', 'amount' => 2500],
                ['code' => 'glo_10gb_30', 'name' => '10GB - 30 Days', 'amount' => 5000],
            ],
            '9mobile' => [
                ['code' => '9mobile_1gb_30', 'name' => '1GB - 30 Days', 'amount' => 1000],
                ['code' => '9mobile_2gb_30', 'name' => '2GB - 30 Days', 'amount' => 2000],
                ['code' => '9mobile_5gb_30', 'name' => '5GB - 30 Days', 'amount' => 2500],
                ['code' => '9mobile_10gb_30', 'name' => '10GB - 30 Days', 'amount' => 5000],
            ],
        ];

        return [
            'success' => true,
            'data' => $plans[$networkCode] ?? []
        ];
    }

    /**
     * Validate network code
     */
    public function isValidNetworkCode(string $networkCode): bool
    {
        $validNetworks = ['mtn', 'airtel', 'glo', '9mobile'];
        return in_array(strtolower($networkCode), $validNetworks);
    }

    /**
     * Get network name from code
     */
    public function getNetworkName(string $networkCode): string
    {
        $networks = [
            'mtn' => 'MTN',
            'airtel' => 'Airtel',
            'glo' => 'GLO',
            '9mobile' => '9Mobile'
        ];

        return $networks[strtolower($networkCode)] ?? strtoupper($networkCode);
    }

    /**
     * Error response helper
     */
    private function errorResponse(string $message): array
    {
        return [
            'success' => false,
            'error' => ['message' => $message]
        ];
    }
}