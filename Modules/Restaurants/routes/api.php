<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurants\Http\Controllers\MenuItemController;
use Modules\Restaurants\Http\Controllers\RestaurantsController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {

    /**Restaurant */
    Route::apiResource('restaurants', RestaurantsController::class);

});

Route::post('/restaurant-request',[RestaurantsController::class,'registerAsOwner']);

/**Menu Restaurant */
Route::get('/restaurant_menuItem/{id}',[MenuItemController::class,'MenuItems']);
