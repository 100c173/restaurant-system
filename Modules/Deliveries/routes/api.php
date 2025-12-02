<?php

use Illuminate\Support\Facades\Route;
use Modules\Deliveries\Http\Controllers\DeliveriesController;



Route::post('/delivery/request-otp', [DeliveriesController::class, 'sendOtp'])->name('delivery.sendOtp');
Route::post('/delivery/verifyOtp', [DeliveriesController::class, 'verifyOtp'])->name('delivery.verifyOtp');
Route::post('/delivery/complete-request', [DeliveriesController::class, 'DeliveryRequest'])->name('delivery.complete-request');