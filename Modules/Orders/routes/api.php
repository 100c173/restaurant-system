<?php

use Illuminate\Support\Facades\Route;
use Modules\Orders\Http\Controllers\OrdersController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('checkout',[OrdersController::class,'store']);
});
