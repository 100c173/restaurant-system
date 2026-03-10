<?php

namespace App\Services;

use App\Models\RestaurantRequest;


class RequestService
{
    public function makeRestaurantRequest(array $data, $logo = null, $images = [])
    {
        // Create restaurantRequest
        $restaurantRequest = RestaurantRequest::create([
            'customer_id' => auth()->id(),
            'address' => $data['address'],
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'restaurant_name' => $data['restaurant_name'],
            'restaurant_phone' => $data['restaurant_phone'] ?? null,
        ]);


        if ($logo) {
            $path = $logo->store('restaurant-request', 'public');
            $restaurantRequest->restaurantLogo()->create([
                'path' => $path,
                'type' => 'logo'
            ]);
        }

        if ($images) {
            foreach ($images as $image) {
                $path = $image->store('restaurant_requests', 'public');

                $restaurantRequest->restaurantImages()->create([
                    'path' => $path,
                    'type' => 'gallery'
                ]);

            }
        }
        return $restaurantRequest;
    }
}
