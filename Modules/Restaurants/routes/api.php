<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurants\Http\Controllers\MenuItemController;
use Modules\Restaurants\Http\Controllers\RestaurantsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    Route::post('restaurants/request', [RestaurantsController::class, 'restaurantRequest'])
        ->middleware(['role:restaurant-owner'])
        ->name('restaurants.request');

    /**Restaurant */
    Route::apiResource('restaurants', RestaurantsController::class)->names('restaurants');


});

    /**Menu Restaurant */
    Route::get('/restaurant_menuItem/{id}',[MenuItemController::class,'MenuItems'])->name('menu.restaurant');
