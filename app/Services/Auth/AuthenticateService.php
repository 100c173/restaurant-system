<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthenticateService
{
    public function register(array $data)
    {
        // Create user with hashed password
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // assigni user role
        $user->assignRole('customer');

        // Generate limited-scope token
        $token = $user->createToken(
            name: 'auth_token',
            expiresAt: now()->addHours(24) // Token expiration
        )->plainTextToken;

        return [$user, $token];
    }



    public function login(array $credentials)
    {

        $user = User::where('email', $credentials['email'])->first();

        // Verify credentials and account status
        if (!$user ) {
            throw ValidationException::withMessages([
                'email' => ['User not found. Please check your email.'],
            ]);
        }
        if(!Hash::check($credentials['password'], $user->password))
        {
            throw ValidationException::withMessages([
                'password' => ['Incorrect password'],
            ]);
        }

        // Generate new token with expiration
        $token = $user->createToken(
            name: 'auth_token',
            expiresAt: now()->addHours(24) // Token expiration
        )->plainTextToken;

        return [$user, $token];
    }

    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();
    }

    public function refreshToke(Request $request)
    {
        //gate the current token 
        $user = $request->user();

        if (!$user) {
            return response()->json(["message" => 'Unauthenticated'], 401);
        }

        //delete all tokens related with the current user
        $user()->currentAccessToken()->delete();

        //create a new token
        $newToken = $user->createToken(
            name: 'auth_token',
            expiresAt: now()->addHours(24),
        )->plainTextToken;

        return [$newToken];
    }
}
