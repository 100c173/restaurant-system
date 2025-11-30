<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\AuthenticationRequest\LoginUserRequest;
use App\Http\Requests\AuthenticationRequest\StoreUserRequest;

class AuthenticationController extends Controller
{
    /**
     * Register new user
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ValidationException
     */
    public function Register(StoreUserRequest $request): JsonResponse
    {

        // Validate with strong password rules
        $validated = $request->validated();

        // Create user with hashed password
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);

        // assigni user role
        $user->assignRole('customer');

        // Generate limited-scope token
        $token = $user->createToken(
            name: 'auth_token',
            expiresAt: now()->addHours(24) // Token expiration
        )->plainTextToken;



        return response()->json([
            'message' => "Registered Successfully",
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => '24 hours',
            'role'       => $user->getRoleNames(),
            'user' => $user->only('id', 'name', 'email'),
        ], 201);
    }

    /**
     * Authenticate user
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ValidationException
     */
    public function login(LoginUserRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $user = User::where('email', $credentials['email'])->first();

        // Verify credentials and account status
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect'],
            ]);
        }

        // Generate new token with expiration
        $token = $user->createToken(
            name: 'auth_token',
            expiresAt: now()->addHours(24) // Token expiration
        )->plainTextToken;

        return response()->json([
            'message' => "Login successful",
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => '24 hours',
            'roles'      => $user->getRoleNames(),
            'user'       => $user->only('id', 'name', 'email'),
        ]);
    }

    /**
     * Revoke current access token
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get authenticated user data
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request): JsonResponse
    {
        // Return only essential user information
        return response()->json([
            'user' => $request->user()->only('id', 'name', 'email')
        ]);
    }

    public function refreshToke(Request $request): JsonResponse
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

        return response()->json([
            'message' => 'Token refreshed successfully',
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => '24 hours',
        ]);

    }
}
