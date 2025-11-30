<?php

namespace App\Filament\Resources\DeliveryRequests\Pages;

use App\Filament\Resources\DeliveryRequests\DeliveryRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Deliveries\Events\DeliveryRequestStatusChanged;
use Modules\Deliveries\Notifications\DeliveryRequestStatusUpdated;

class EditDeliveryRequest extends EditRecord
{
    protected static string $resource = DeliveryRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave() : void {
        $deliveryRequest = $this->record ;
        $deliveryRequest->customer->notify(new DeliveryRequestStatusUpdated($deliveryRequest));

        DeliveryRequestStatusChanged::dispatch($deliveryRequest);
    }
}
