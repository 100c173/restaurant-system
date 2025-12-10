<?php

use Illuminate\Support\Facades\Route;
use Modules\Deliveries\Http\Controllers\DeliveriesController;


Route::post('/delivery/complete-request', [DeliveriesController::class, 'DeliveryRequest'])->name('delivery.complete-request');