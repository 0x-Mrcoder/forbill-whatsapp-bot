<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vtu_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Provider name (e.g., MTN, Airtel, GLO, 9Mobile)
            $table->string('code')->unique(); // Network code (mtn, airtel, glo, 9mobile)
            $table->string('api_endpoint'); // Provider API endpoint
            $table->text('api_key')->nullable(); // Encrypted API key
            $table->text('secret_key')->nullable(); // Encrypted secret key
            $table->enum('service_type', ['airtime', 'data', 'both'])->default('both');
            $table->boolean('is_active')->default(true);
            $table->decimal('commission_rate', 5, 4)->default(0.02); // 2% commission
            $table->json('settings')->nullable(); // Additional provider settings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vtu_providers');
    }
};
