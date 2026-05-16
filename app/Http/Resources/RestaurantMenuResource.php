<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Restaurants\Models\MenuItem;
use Stancl\Tenancy\Facades\Tenancy;

class RestaurantMenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        ['restaurant' => $restaurant, 'categories' => $categories] = $this->resource;

        return [
            'restaurant' => [
                'id' => $restaurant->id,
                'name' => $restaurant->name,
                'description' => $restaurant->description,
                'logo' => $restaurant->logo,
                'cover_image' => $restaurant->cover_image,
                'address' => $restaurant->address,
                'phone' => $restaurant->phone,
                'email' => $restaurant->email,
                'is_open_now' => $restaurant->isOpenNow(),
                'opening_time' => $restaurant->opening_time,
                'closing_time' => $restaurant->closing_time,
            ],

            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'items' => ItemResource::collection(
                        $category->menuItems->map(fn($item) => [
                            'item' => $item,
                            'restaurant' => $this->resource['restaurant'],
                        ])
                    )->values(),
                ];
            }),
        ];
    }
}
