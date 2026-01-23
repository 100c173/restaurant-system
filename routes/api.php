<?php

use App\Http\Controllers\AuthenticationController;
use Illuminate\Http\Request;
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
Route::post('/verify-otp-email', [AuthenticationController::class, 'verifyRegisterOtp']);

// Verify OTP code
Route::post('/verify-otp-password', [AuthenticationController::class, 'verifyPasswordOtp']);


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
});
