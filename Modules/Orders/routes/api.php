<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware(['auth:sanctum','phone_number_exist'])->group(function () {
    Route::post('/cart/restaurant/confirme',[OrdersController::class,'store']); // confirme 
    Route::post('/orders/checkout',[OrdersController::class,'checkout']);
    
    Route::get('/order/details/{reference_number}',[OrdersController::class,'orderDetails']);
    Route::get('/orders/order_cost/{reference_number}',[OrdersController::class,'orderCost']);
    Route::get('/orders/cancel/{reference_number}',[OrdersController::class,'cancelOrder']);
});
