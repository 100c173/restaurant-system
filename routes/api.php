<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/**
** Authentication User Login/Register/Logout/Me
*/

Route::post('/login',[AuthenticationController::class,'login'])->middleware('throttle:5,1');;
Route::post('/register',[AuthenticationController::class,'Register']);


Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout',[AuthenticationController::class,'logout'])->name('auth.logout');
    Route::get('/me',[AuthenticationController::class,'user'])->name('auth.me');
    Route::post('token/refresh', [AuthenticationController::class, 'refreshToken']);
});

