<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;




use App\Http\Requests\AuthenticationRequest\SetPasswordRequest;
use App\Http\Requests\SendOtpRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Models\User;
use App\Services\Auth\AuthenticateService;
use App\Services\OtpCode\OtpCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Http\Requests\AuthenticationRequest\LoginUserRequest;
use App\Http\Requests\AuthenticationRequest\StoreUserRequest;

class AuthenticationController extends Controller
{

    public function __construct(
        public AuthenticateService $authService,
        public OtpCodeService $otpCodeService,
    ) {
    }


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

        $user = $this->authService->registerAsCustomer($validated);

        $this->otpCodeService->sendOtp([
            'email' => $validated['email'],
            'purpose' => 'register'
        ]);

        return self::success(
            data: [
                'user' => $user, // user UserResource
            ],
            message: "Registration successful. Please verify your email.",
            status: 201
        );

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

        [$user, $token] = $this->authService->login($credentials);

        return self::success(
            [
                'access_token' => $token,
                'token_type' => 'Bearer',
                'expires_in' => '24 hours',
                'roles' => $user->getRoleNames(),
                'user' => $user->only('id', 'name', 'email'),
            ],
            'Login successful',
            200
        );


    }

    /**
     * Revoke current access token
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request);
        return self::success(null, 'Successfully logged out');
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
        return self::success([
            'user' => $request->user()->only('id', 'name', 'email'),
        ]);
    }

    public function refreshToke(Request $request): JsonResponse
    {
        [$newToken] = $this->authService->refreshToke($request);

        return response()->json([
            'message' => 'Token refreshed successfully',
            'access_token' => $newToken,
            'token_type' => 'Bearer',
            'expires_in' => '24 hours',
        ]);

    }

    public function sendOtp(SendOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $this->otpCodeService->sendOtp($data);

        return self::success($data['email']);

    }
    public function verifyRegisterOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->otpCodeService->verifyOtpForRegister($data);

        if (!$user) {
            return self::error('Invalid or expired OTP code.');
        }

        $token = $this->authService->createTokenWithExpiration($user);

        return self::success(['user' => $user, 'token' => $token], "OTP verified successfully.");
    }

    public function verifyPasswordOtp(VerifyOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $reset_token = $this->otpCodeService->verifyOtpForPassword($data);

        if (!$reset_token) {
            return self::error('Invalid or expired OTP code.');
        }

        return self::success(['reset_token' => $reset_token]);
    }


    public function setPassword(SetPasswordRequest $request)
    {
        $data = $request->validated();

        $user = $this->authService->resetPassword($data);
        if ($user) {
            return self::success(['user' => $user], 'Password has been reset successfully.', 201);
        } else {
            return self::error('Invalid or expired token.', 403);
        }
    }
}
