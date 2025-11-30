<?php

namespace App\Filament\Resources\DeliveryRequests\Pages;

use App\Filament\Resources\DeliveryRequests\DeliveryRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDeliveryRequest extends ViewRecord
{
    protected static string $resource = DeliveryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
