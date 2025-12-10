<?php

namespace Modules\Deliveries\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Modules\Deliveries\Events\UserRequestedToBecomeDriver;
use Modules\Deliveries\Models\DeliveryRequest;


class DeliveryService
{

    public function saveDeliveryRequest($request)
    {
        $data = $request->validated();

        // Search for the user if they already exist
        $user = User::where('email', $data['email'])->first();

        // If the user is present and registered with the regular application
        if ($user) {

            // We make sure that the entered password matches the real password
            if (!Hash::check($data['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['You already have an account with this email. Please enter your correct password or use another email.'],
                ]);
            }

            // Remove the password from the request — because it is not required now 
            unset($data['password']);

            // We link the request to the user
            $data['user_id'] = $user->id;
        }

        // Get the current order history via email (because the OTP has already been verified)
        $deliveryRequest = DeliveryRequest::where('email', $data['email'])->firstOrFail();


        // Save order files
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

        // Update final order data
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
            'status' => 'pending', 
            'password' => $data['password'] ?? null,
            'user_id' => $user->id ?? null,
        ]);

        // Dispatch event to notify management
        UserRequestedToBecomeDriver::dispatch($deliveryRequest);

        return $deliveryRequest;
    }

}
