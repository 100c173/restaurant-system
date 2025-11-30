<?php

namespace App\Filament\Owner\Resources\Orders\Pages;

use App\Filament\Owner\Resources\Orders\OrderResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Orders\Notifications\OrderStatusUpdated;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(){
        $order = $this->record ;
        $order->customer?->notify(new OrderStatusUpdated($order));
    }
}
