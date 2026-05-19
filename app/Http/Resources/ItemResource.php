<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemVariant;
use Stancl\Tenancy\Facades\Tenancy;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        ['item' => $item, 'restaurant' => $restaurant, 'details' => $details] = $this->resource;
        Tenancy::initialize($this->resource['restaurant']['tenant_id']);

        $data = [
            'id' => $item->id,
            'restaurant_id' => $restaurant->id,
            'name' => $item->name,
            'description' => $item->description,
            //'price' => $item->startingPrice(),
            'image' => $item->image,
            'is_featured' => $item->is_featured,
            'preparation_time' => $item->formattedPreparationTime(),
        ];

        if ($details)
            $data['variants'] = MenuItemVariantResource::collection(
                $item->variants->map(fn($variant) => [
                    'variant' => $variant,
                    'restaurant' => $this->resource['restaurant'],
                ])
            )->values();

        Tenancy::end();

        return $data;

    }
}
