<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware(['auth:sanctum','phone_number_exist'])->group(function () {
    Route::post('/cart/restaurant/checkout',[OrdersController::class,'store']);
});
