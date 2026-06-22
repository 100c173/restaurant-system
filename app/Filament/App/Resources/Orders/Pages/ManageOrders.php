<?php

namespace App\Filament\App\Resources\Orders\Pages;

use App\Filament\App\Resources\Orders\OrdersResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageOrders extends ManageRecords
{
    protected static string $resource = OrdersResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
