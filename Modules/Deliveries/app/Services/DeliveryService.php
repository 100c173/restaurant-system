<?php

namespace Modules\Deliveries\Services;

use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Modules\Deliveries\Events\UserRequestedToBecomeDriver;
use Modules\Deliveries\Models\DeliveryRequest;
use Modules\Deliveries\Notifications\SendOtpNotification;

class DeliveryService
{
    public function sendOtp(array $data)
    {
        $email = $data['email'];
        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(15);

        DeliveryRequest::updateOrCreate(
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
        $otpCode = $data['otp_code'];

        $deliveryRequest = DeliveryRequest::where('email', $email)
            ->where('otp_code', $otpCode)
            ->where('otp_expires_at', '>', Carbon::now())
            ->first();

        if ($deliveryRequest) {

            $deliveryRequest->update([
                'email_verified_at' => now(),
            ]);
            $this->resetCode($deliveryRequest);
        }
        return $deliveryRequest;
    }

    public function resetCode(DeliveryRequest $deliveryRequest)
    {
        $deliveryRequest->otp_expires_at = null;
        $deliveryRequest->otp_code = null;
        $deliveryRequest->save();
    }



    public function saveDeliveryRequest($request)
    {
        $data = $request->validated();

        // البحث عن المستخدم إذا كان موجود مسبقًا
        $user = User::where('email', $data['email'])->first();

        // لو المستخدم موجود ومسجل بالتطبيق العادي
        if ($user) {

            // نتأكد أن كلمة السر المدخلة تطابق الباسورد الحقيقي
            if (!Hash::check($data['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['You already have an account with this email. Please enter your correct password or use another email.'],
                ]);
            }

            // نزيل كلمة السر من الطلب — لأنها ليست مطلوبة الآن
            unset($data['password']);

            // نربط الطلب بالمستخدم
            $data['user_id'] = $user->id;
        }

        // الحصول على سجل الطلب الحالي عبر الإيميل (لأن الـ OTP سبق وتم التحقق)
        $deliveryRequest = DeliveryRequest::where('email', $data['email'])->firstOrFail();

        if (!$deliveryRequest->email_verified_at) {
            throw ValidationException::withMessages([
                'error' => 'Email is not verified'
            ]);
        }

        // حفظ ملفات الطلب
        $pathPrefix = 'uploads/delivery/';

        if ($request->hasFile('personal_photo')) {
            $data['personal_photo'] = $request->file('personal_photo')->store($pathPrefix, 'public');
        }

        if ($request->hasFile('id_card_front')) {
            $data['id_card_front'] = $request->file('id_card_front')->store($pathPrefix, 'public');
        }

        if ($request->hasFile('id_card_back')) {
            $data['id_card_back'] = $request->file('id_card_back')->store($pathPrefix, 'public');
        }

        if ($request->hasFile('driving_license')) {
            $data['driving_license'] = $request->file('driving_license')->store($pathPrefix, 'public');
        }

        if ($request->hasFile('vehicle_photo')) {
            $data['vehicle_photo'] = $request->file('vehicle_photo')->store($pathPrefix, 'public');
        }

        // تحديث بيانات الطلب النهائي
        $deliveryRequest->update([
            'name' => $data['name'],
            'phone_number' => $data['phone_number'],
            'national_id' => $data['national_id'] ?? null,
            'city' => $data['city'] ?? null,
            'address' => $data['address'] ?? null,
            'personal_photo' => $data['personal_photo'] ?? $deliveryRequest->personal_photo,
            'id_card_front' => $data['id_card_front'] ?? $deliveryRequest->id_card_front,
            'id_card_back' => $data['id_card_back'] ?? $deliveryRequest->id_card_back,
            'driving_license' => $data['driving_license'] ?? $deliveryRequest->driving_license,
            'vehicle_type' => $data['vehicle_type'] ?? null,
            'vehicle_number' => $data['vehicle_number'] ?? null,
            'vehicle_photo' => $data['vehicle_photo'] ?? null,
            'status' => 'pending', // الطلب ينتظر موافقة الإدارة
            'password' => $data['password'] ?? null,
            'user_id' => $user->id ?? null,
        ]);

        // Dispatch event لإعلام الإدارة
        UserRequestedToBecomeDriver::dispatch($deliveryRequest);

        return $deliveryRequest;
    }

}
