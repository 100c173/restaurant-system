<?php

namespace App\Filament\App\Resources\MenuItems\Pages;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMenuItem extends CreateRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Menu item created')
            ->body('The item has been added to the menu successfully.');
    }
}
