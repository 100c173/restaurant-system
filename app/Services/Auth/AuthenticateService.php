<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Modules\Deliveries\Notifications\SendOtpNotification;
use Str;


class AuthenticateService
{
    public function registerAsCustomer(array $data)
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
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['User not found. Please check your email.'],
            ]);
        }
        if (!Hash::check($credentials['password'], $user->password)) {
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
    public function sendOtp(array $data)
    {
        $email = $data['email'];
        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        OtpCode::updateOrCreate(
            ['email' => $email],
            [
                'otp_hash' => $otpCode,
                'otp_expires_at' => $expiresAt,
                'verified_at' => null,
                'reset_token' => null,
            ]
        );

        Notification::route('mail', $email)->notify(new SendOtpNotification($otpCode));

        return $expiresAt;
    }

    public function verifyOtp(array $data)
    {
        $email = $data['email'];
        $code = $data['otp_code'];
        $purpose = $data['purpose'] ?? 'reset_password';

        $otpCode = OtpCode::where('email', $email)
            ->where('otp_expires_at', '>', Carbon::now())
            ->first();

        if ($otpCode && Hash::check($code, $otpCode->otp_hash)) {
            if ($purpose === 'register') {

                User::where('email', $email)->update(['email_verified_at' => now()]);
                $otpCode->update([
                    'otp_hash' => null,
                    'otp_expires_at' => null,
                    'verified_at' => null,
                    'reset_token' => null,
                ]);
                return true;
            } elseif ($purpose === 'reset_password') {

                $resetToken = Str::random(60);
                $otpCode->update(['verified_at' => now(), 'reset_token' => $resetToken]);
                return $resetToken;
            }
        }

        return null;
    }

    public function resetPassword(array $data)
    {
        $resetToken = $data['reset_token'];
        $newPassword = $data['new_password'];

        $otpCode = OtpCode::where('reset_token', $resetToken)
            ->where('verified_at', '!=', null)

            ->where('otp_expires_at', '>', Carbon::now())
            ->first();

        if (!$otpCode) {
            abort(403, 'Invalid or expired reset token.');
        }

        $user = User::where('email', $otpCode->email)->firstOrFail();

        $user->update([
            'password' => Hash::make($newPassword),
        ]);

        $user->notify(new PasswordChangedNotification());

        // علّم الطلب بأنه مستخدم لمنع إعادة استخدام التوكن
        $otpCode->update([
            'reset_token' => null,
            'otp_hash' => null,
            'otp_expires_at' => null,
            'verified_at' => null,
        ]);

        return $user;
    }
}
