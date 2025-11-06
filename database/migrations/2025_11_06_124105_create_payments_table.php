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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // Payment reference
            $table->foreignId('transaction_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('gateway', ['paystack', 'flutterwave', 'wallet'])->default('paystack');
            $table->decimal('amount', 12, 2); // Payment amount
            $table->decimal('gateway_fee', 12, 2)->default(0); // Gateway processing fee
            $table->string('gateway_reference')->nullable(); // Gateway transaction reference
            $table->enum('status', ['pending', 'processing', 'success', 'failed', 'cancelled'])->default('pending');
            $table->string('authorization_url')->nullable(); // Payment link for card payments
            $table->string('access_code')->nullable(); // Gateway access code
            $table->text('gateway_response')->nullable(); // Raw gateway response
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['reference']);
            $table->index(['transaction_id']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
