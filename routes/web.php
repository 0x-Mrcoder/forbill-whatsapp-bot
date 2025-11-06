<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
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
    try {
        // Simple health check without database dependency
        return response()->json([
            'status' => 'healthy',
            'timestamp' => now(),
            'memory_usage' => memory_get_usage(true),
            'app_env' => config('app.env')
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ], 500);
    }
});

Route::get('/db-health', function () {
    try {
        DB::connection()->getPdo();
        return response()->json([
            'status' => 'healthy',
            'database' => 'connected',
            'timestamp' => now()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'status' => 'error',
            'database' => 'disconnected',
            'error' => $e->getMessage()
        ], 500);
    }
});

// WhatsApp webhook routes
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle']);

// Test route for sending messages
Route::post('/test/whatsapp', [WhatsAppWebhookController::class, 'test']);
