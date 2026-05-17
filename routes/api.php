<?php

use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Authentication Routes
|--------------------------------------------------------------------------
| These routes are accessible without authentication.
| Used for registering, logging in, requesting OTP, verifying OTP,
| and resetting passwords.
|--------------------------------------------------------------------------
*/

// Register as a Customer new account
Route::post('/register', [AuthenticationController::class, 'register'])
->middleware('throttle:5,1');

// Login with rate limiting (max 5 attempts per minute)
Route::post('/login', [AuthenticationController::class, 'login'])
    ->middleware('throttle:5,1');

// Request OTP for email verification or password reset
Route::post('/request-otp', [AuthenticationController::class, 'sendOtp']);

// Verify OTP code 
Route::post('/verify-otp-email', [AuthenticationController::class, 'verifyOtp']);


// Reset password (only after email is verified)
Route::post('/set-password', [AuthenticationController::class, 'setPassword'])
->middleware('throttle:5,1');


/*
|--------------------------------------------------------------------------
| Protected Routes (Require Authentication)
|--------------------------------------------------------------------------
| These routes require a valid Sanctum token.
| Users must be logged in to access them.
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {

    // Logout and revoke active token
    Route::post('/logout', [AuthenticationController::class, 'logout']);
    // Get the authenticated user's information
    Route::get('/me', [AuthenticationController::class, 'user']);

    // Refresh an expired/expiring token (if applicable in your logic)
    Route::post('/token/refresh', [AuthenticationController::class, 'refreshToken']);

    // To be tenat 
    Route::post('/restaurant-request',[RequestController::class,'restaurantRequest']) ;

    //Add to cart
    Route::post('/cart', [CartController::class, 'store']);   // add item
    Route::delete('/cart/{item_id}',[CartController::class, 'destroy']); // remove item
    Route::delete('/cart' , [CartController::class, 'clear']); // clear the cart
});


// get home data : restaurant + restaurant-categories
Route::get('/home',[HomeController::class,'getHomeData']);

//get restaurant by restaurant
Route::get('/categories/{category}/restaurants', [HomeController::class, 'restaurantByCategory']);

//get restaurant menu items
Route::get('restaurants/{restaurant}/menu', [RestaurantController::class, 'menu']);

//get all restaurant-categories
Route::get('restaurant-categories',[HomeController::class,'categories']);

//get item details (cached 5 minutes)
Route::get('/restaurants/{restaurant}/menu/{menu_item}', [RestaurantController::class, 'getItem']);

