<?php

use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\ShipmentWebhookController;
use App\Http\Middleware\ValidateTrackingNumber;
use Illuminate\Support\Facades\Route;

Route::get('/shipments/{tracking_number}', [ShipmentController::class, 'get'])
    ->middleware(ValidateTrackingNumber::class);
Route::post('/webhooks/shippo', [ShipmentWebhookController::class, 'submitShippo']);
