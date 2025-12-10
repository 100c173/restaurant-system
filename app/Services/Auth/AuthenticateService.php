<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Modules\Deliveries\Notifications\SendOtpNotification;

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
    public function sendOtp(array $data)
    {
        $email = $data['email'];
        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        OtpCode::updateOrCreate(
            attributes: ['email' => $email],
            values: [
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
            ]
        );
        
        Notification::route('mail', $email)->notify(new SendOtpNotification($otpCode));
    
        return $expiresAt;
    }

    public function verifyOtp(array $data)
    {
        $email = $data['email'];
        $code = $data['otp_code'];

        $otpCode = OtpCode::where('email', $email)
            ->where('otp_code', $code)
            ->where('otp_expires_at', '>', Carbon::now())
            ->first();

        if ($otpCode) {
            $user = User::where('email',$email )->first();
            $user->update([
                'email_verified_at' => now(),
            ]);
            $this->resetCode($otpCode);
        }
        return $otpCode;
    }

    public function resetCode(OtpCode $otpCode)
    {
        $otpCode->otp_expires_at = null;
        $otpCode->otp_code = null;
        $otpCode->save();
    }

    public function resetPassword(array $data)
    {
        $user = User::where('email' , $data['email'])->first();

        $user->update([
            'password' => Hash::make($data['new_password']),
        ]);

        $user->notify(new PasswordChangedNotification());

        return $user;

    }
}
