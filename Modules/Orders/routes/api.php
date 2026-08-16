<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware(['auth:sanctum','phone_number_exist'])->group(function () {
    Route::post('/cart/restaurant/confirme',[OrdersController::class,'store']); // confirme order
    Route::post('/orders/checkout',[OrdersController::class,'checkout']);// checlout order

    Route::get('/order/details/{reference_number}',[OrdersController::class,'orderDetails']);//get order details
    Route::get('/orders/order_cost/{reference_number}',[OrdersController::class,'orderCost']);//get order cost
    Route::post('/orders/cancel',[OrdersController::class,'cancelOrder']);// cancel order
});
