<?php

namespace App\Filament\Owner\Resources\Restaurants\Pages;

use App\Filament\Owner\Resources\Restaurants\RestaurantResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRestaurant extends CreateRecord
{
    protected static string $resource = RestaurantResource::class;
}
