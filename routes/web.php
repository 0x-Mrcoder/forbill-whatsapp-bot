<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppWebhookController;

Route::get('/', function () {
    return view('welcome');
});

// WhatsApp webhook routes
Route::get('/webhook/whatsapp', [WhatsAppWebhookController::class, 'verify']);
Route::post('/webhook/whatsapp', [WhatsAppWebhookController::class, 'handle']);

// Test route for sending messages
Route::post('/test/whatsapp', [WhatsAppWebhookController::class, 'test']);
