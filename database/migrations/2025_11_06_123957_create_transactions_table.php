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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // Unique transaction reference
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('vtu_provider_id')->constrained()->onDelete('cascade');
            $table->string('recipient_phone'); // Phone number to receive airtime/data
            $table->enum('service_type', ['airtime', 'data', 'electricity', 'tv']);
            $table->string('network_code'); // mtn, airtel, glo, 9mobile
            $table->decimal('amount', 12, 2); // Amount in Naira
            $table->decimal('commission', 12, 2)->default(0); // ForBill commission
            $table->decimal('provider_amount', 12, 2); // Amount sent to provider
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->string('provider_reference')->nullable(); // Provider transaction reference
            $table->text('provider_response')->nullable(); // Raw provider response
            $table->string('payment_reference')->nullable(); // Payment gateway reference
            $table->enum('payment_method', ['wallet', 'card', 'bank'])->default('card');
            $table->text('failure_reason')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->json('metadata')->nullable(); // Additional transaction data
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['reference']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
