<?php

namespace Database\Seeders;

use App\Models\VtuProvider;
use Illuminate\Database\Seeder;

class VtuProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'name' => 'MTN Nigeria',
                'code' => 'mtn',
                'api_endpoint' => 'https://api.mtnvtu.com/v1',
                'api_key' => 'mtn_test_key_123',
                'secret_key' => 'mtn_test_secret_456',
                'service_type' => 'both',
                'is_active' => true,
                'commission_rate' => 0.02,
                'settings' => [
                    'min_amount' => 50,
                    'max_amount' => 50000,
                    'supports_data' => true,
                    'supports_airtime' => true,
                ]
            ],
            [
                'name' => 'Airtel Nigeria',
                'code' => 'airtel',
                'api_endpoint' => 'https://api.airtelvtu.com/v1',
                'api_key' => 'airtel_test_key_123',
                'secret_key' => 'airtel_test_secret_456',
                'service_type' => 'both',
                'is_active' => true,
                'commission_rate' => 0.02,
                'settings' => [
                    'min_amount' => 50,
                    'max_amount' => 50000,
                    'supports_data' => true,
                    'supports_airtime' => true,
                ]
            ],
            [
                'name' => 'GLO Nigeria',
                'code' => 'glo',
                'api_endpoint' => 'https://api.glovtu.com/v1',
                'api_key' => 'glo_test_key_123',
                'secret_key' => 'glo_test_secret_456',
                'service_type' => 'both',
                'is_active' => true,
                'commission_rate' => 0.02,
                'settings' => [
                    'min_amount' => 50,
                    'max_amount' => 50000,
                    'supports_data' => true,
                    'supports_airtime' => true,
                ]
            ],
            [
                'name' => '9Mobile Nigeria',
                'code' => '9mobile',
                'api_endpoint' => 'https://api.9mobilevtu.com/v1',
                'api_key' => '9mobile_test_key_123',
                'secret_key' => '9mobile_test_secret_456',
                'service_type' => 'both',
                'is_active' => true,
                'commission_rate' => 0.02,
                'settings' => [
                    'min_amount' => 50,
                    'max_amount' => 50000,
                    'supports_data' => true,
                    'supports_airtime' => true,
                ]
            ],
        ];

        foreach ($providers as $providerData) {
            VtuProvider::updateOrCreate(
                ['code' => $providerData['code']],
                $providerData
            );
        }
    }
}
