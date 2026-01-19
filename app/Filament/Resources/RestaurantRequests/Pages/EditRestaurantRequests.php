<?php

namespace App\Filament\Resources\RestaurantRequests\Pages;

use App\Filament\Resources\RestaurantRequests\RestaurantRequestsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRestaurantRequests extends EditRecord
{
    protected static string $resource = RestaurantRequestsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
