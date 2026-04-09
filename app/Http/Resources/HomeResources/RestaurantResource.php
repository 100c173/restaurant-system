<?php

namespace App\Http\Resources\HomeResources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'phone' => $this->phone,
            'logo' => $this->logo ? asset("storage/{$this->logo}") : null,
            'cover_image' => $this->cover_image ? asset("storage/{$this->cover_image}") : null,

            'hours' => [
                'opens' => Carbon::parse($this->opening_time)->format('h:i A'), 
                'closes' => Carbon::parse($this->closing_time)->format('h:i A'),
                'is_open' => $this->isOpenNow(),
                // 09:00:00  -> 09:00 AM
                // 23:00:00  -> 11:00 PM
                
            ],
            'location' => [
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,

                // distance is only present when using nearby() scope
                'distance_km' => isset($this->distance)
                    ? round($this->distance, 2)
                    : null,
            ],

            'categories' => CategoryResource::collection($this->whenLoaded('categories')),
            // 'is_featured' => $this->is_featured,

            'rate' => $this->rate,
        ];
    }
}
