<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurants\Http\Controllers\RestaurantsController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('restaurants', RestaurantsController::class)->names('restaurants');
});
