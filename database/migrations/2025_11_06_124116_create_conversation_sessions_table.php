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
        Schema::create('conversation_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('whatsapp_phone'); // User's WhatsApp phone number
            $table->enum('current_step', [
                'idle', 'awaiting_service_type', 'awaiting_amount', 'awaiting_phone', 
                'awaiting_confirmation', 'awaiting_payment', 'processing'
            ])->default('idle');
            $table->json('context_data')->nullable(); // Current conversation context
            $table->string('current_transaction_id')->nullable(); // Current transaction being processed
            $table->timestamp('last_activity_at')->useCurrent();
            $table->timestamp('expires_at')->nullable(); // Session expiry
            $table->timestamps();
            
            $table->unique(['user_id']);
            $table->index(['whatsapp_phone']);
            $table->index(['current_step']);
            $table->index(['last_activity_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_sessions');
    }
};
