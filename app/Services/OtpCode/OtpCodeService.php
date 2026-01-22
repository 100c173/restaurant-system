<?php

namespace App\Services\OtpCode;
use App\Models\OtpCode;
use App\Models\PasswordResetToken;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use App\Exceptions\OtpSendFailedException;
use Modules\Deliveries\Notifications\SendOtpNotification;
use Str;
use Throwable;

class OtpCodeService
{
    public function sendOtp(array $data)
    {
        try {
            $email = $data['email'];
            $purpose = $data['purpose'];

            $otpCode = random_int(100000, 999999);
            $expiresAt = now()->addMinutes(10);

            OtpCode::updateOrCreate(
                [
                    'email' => $email,
                    'purpose' => $purpose,
                ],
                [
                    'otp_hash' => Hash::make($otpCode),
                    'expires_at' => $expiresAt,
                ]
            );

            Notification::route('mail', $email)
                ->notify(new SendOtpNotification($otpCode));

        } catch (Throwable $e) {
            throw new OtpSendFailedException();
        }
    }

    public function verifyOtpForRegister(array $data)
    {
        $email = $data['email'];
        $code = $data['otp_code'];
        $purpose = 'register';

        $otp = OtpCode::where('email', $email)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->first();


        if (!$otp || !Hash::check($code, $otp->otp_hash)) {
            return null;
        }


        $otp->delete();

        $user = User::where('email', $email)->first();
        $user->update(['email_verified_at' => now()]);

        return $user;

    }
    public function verifyOtpForPassword(array $data)
    {
        $email = $data['email'];
        $code = $data['otp_code'];
        $purpose = 'reset_password';

        $otp = OtpCode::where('email', $email)
            ->where('purpose', $purpose)
            ->where('expires_at', '>', now())
            ->first();


        if (!$otp || !Hash::check($code, $otp->otp_hash)) {
            return null;
        }

        $otp->delete();

        $plainToken = Str::random(64);

        PasswordResetToken::updateOrCreate(
            attributes: ['email' => $email],
            values: [
                'token' => Hash::make($plainToken),
                'expires_at' => now()->addMinutes(10),
            ]
        );

        return $plainToken;
    }
}
