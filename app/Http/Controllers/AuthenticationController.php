<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;


use App\Models\User;
use App\Services\Auth\AuthenticateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\AuthenticationRequest\LoginUserRequest;
use App\Http\Requests\AuthenticationRequest\StoreUserRequest;
use App\Http\Requests\AuthenticationRequest\StoreDeliveryRequest;

class AuthenticationController extends Controller
{

    public function __construct(public AuthenticateService $service){}


    /**
     * Register new user
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ValidationException
     */
    public function register(StoreUserRequest $request): JsonResponse
    {
        // Validate with strong password rules
        $validated = $request->validated();

        [$user,$token] = $this->service->register($validated);


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

        [$user,$token] = $this->service->login($credentials);

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
        $this->service->logout($request);   

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
        [$newToken] = $this->service->refreshToke($request);

        return response()->json([
            'message' => 'Token refreshed successfully',
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => '24 hours',
        ]);

    }
}
