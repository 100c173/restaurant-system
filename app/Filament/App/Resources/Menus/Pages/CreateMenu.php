<?php

namespace App\Filament\App\Resources\Menus\Pages;

use App\Filament\App\Resources\Menus\MenuResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    // Redirect to list after creation
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    // Success notification
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Menu created')
            ->body('The menu has been created successfully and is ready to use.');
    }
}