<?php

namespace App\Filament\Resources\DeliveryRequests\Pages;

use App\Filament\Resources\DeliveryRequests\DeliveryRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryRequests extends ListRecords
{
    protected static string $resource = DeliveryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //CreateAction::make(),
        ];
    }
}
