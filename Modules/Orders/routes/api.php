<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware(['auth:sanctum','phone_number_exist'])->group(function () {
    Route::post('/cart/restaurant/confirme',[OrdersController::class,'store']); // confirme 
    Route::post('/orders/checkout',[OrdersController::class,'checkout']);

    Route::get('/orders/deliveryFee/{reference_number}',[OrdersController::class,'deliveryCost']);
    Route::get('/orders/cancel/{reference_number}',[OrdersController::class,'cancelOrder']);
});
