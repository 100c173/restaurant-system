<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\CartItemController;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
  //  Route::apiResource('orders', OrdersController::class)->names('orders');
  
  Route::get('/cart', [CartItemController::class,'index'])->name('cart.items');
  Route::post('/cart',[CartItemController::class,'addToCart'])->name('addToCart');
  Route::delete('/cart',[CartItemController::class,'clearCartItems'])->name('addToCart');
  Route::get('/checkout',[OrdersController::class,'checkout'])->name('checkout.orders');

});
