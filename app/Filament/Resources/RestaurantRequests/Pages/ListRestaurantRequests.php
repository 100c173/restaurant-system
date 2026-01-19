<?php

namespace App\Filament\Resources\RestaurantRequests\Pages;

use App\Filament\Resources\RestaurantRequests\RestaurantRequestsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRestaurantRequests extends ListRecords
{
    protected static string $resource = RestaurantRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
          
        ];
    }
}
