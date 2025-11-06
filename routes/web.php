<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/', function () {
    return response()->json([
        'status' => 'ok',
        'message' => 'ForBill WhatsApp Bot is running',
        'timestamp' => now(),
        'version' => '1.0.0'
    ]);
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'database' => 'connected',
        'timestamp' => now()
    ]);
});

// WhatsApp webhook routes
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle']);

// Test route for sending messages
Route::post('/test/whatsapp', [WhatsAppWebhookController::class, 'test']);
