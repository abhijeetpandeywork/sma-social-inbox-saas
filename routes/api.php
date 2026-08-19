<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\WebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'check']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Webhook Receiver Routes
Route::get('/webhooks/meta', [WebhookController::class, 'verifyMeta']);
Route::post('/webhooks/meta', [WebhookController::class, 'handleMeta']);
