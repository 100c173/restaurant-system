<?php

namespace App\Filament\Owner\Resources\Restaurants\Pages;

use App\Filament\Owner\Resources\Restaurants\RestaurantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestaurants extends ListRecords
{
    protected static string $resource = RestaurantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            
        ];
    }
}
