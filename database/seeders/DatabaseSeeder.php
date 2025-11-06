<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            VtuProviderSeeder::class,
        ]);

        // Create a test user with wallet balance
        User::factory()->create([
            'name' => 'Test User',
            'phone' => '2348012345678',
            'email' => 'test@forbill.com',
            'wallet_balance' => 10000.00,
        ]);
    }
}
