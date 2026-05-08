<?php

namespace App\Services;

use App\Models\RestaurantRequest;
use App\Models\User;


class RequestService
{
    public function makeRestaurantRequest(array $data, $logo = null, $images = [])
    {
        // Create restaurantRequest
        $restaurantRequest = RestaurantRequest::create([
            'customer_id' => auth()->id(),
            'address' => $data['address'],
            'description' => $data['description']??null,
            'phone' => $data['restaurant_phone'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'restaurant_name' => $data['restaurant_name'],
            'categories' => $data['categories']??null ,
        ]);


        $this->notifySuperAdmin();

        return $restaurantRequest;
    }

private function notifySuperAdmin()
{
    $admin = User::where('email', 'admin1@gmail.com')->first();

    \Filament\Notifications\Notification::make()
        ->title('New restaurant request')
        ->body(auth()->user()->name . ' want to be tenant')
        ->success()
        ->sendToDatabase($admin, true);
}
}
