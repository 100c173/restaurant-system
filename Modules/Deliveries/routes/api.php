<?php

use Illuminate\Support\Facades\Route;
use Modules\Deliveries\Http\Controllers\DeliveriesController;



Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::post('delivery-request',[DeliveriesController::class,'DeliveryRequest']);
});
