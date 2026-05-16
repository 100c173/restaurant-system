<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
        ['item' => $item, 'restaurant' => $restaurant] = $this->resource;
        Tenancy::initialize($this->resource['restaurant']['tenant_id']);
        $data = [
            'id' => $item->id,
            'name' => $item->name,
            'description' => $item->description,
            'price' => $item->startingPrice(),
            'image' => $item->image,
            'is_featured' => $item->is_featured,
            'preparation_time' => $item->formattedPreparationTime(),
        ];
        Tenancy::end();

        return $data;

    }
}
