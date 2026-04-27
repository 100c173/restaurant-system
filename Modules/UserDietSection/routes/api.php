<?php

use Illuminate\Support\Facades\Route;
use Modules\UserDietSection\Http\Controllers\FoodAnalysisController;
use Modules\UserDietSection\Http\Controllers\UserDietSectionController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/health-profile', [UserDietSectionController::class, 'store']);

    Route::post('/scan/{type}', [FoodAnalysisController::class, 'scan'])
        ->where('type', 'meal|tabel');
});

