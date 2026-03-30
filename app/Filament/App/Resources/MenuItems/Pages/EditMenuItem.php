<?php

namespace App\Filament\App\Resources\MenuItems\Pages;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMenuItem extends EditRecord
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete menu item')
                ->modalDescription('Are you sure? This action cannot be undone.')
                ->modalSubmitActionLabel('Yes, delete it'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Menu item updated')
            ->body('Your changes have been saved successfully.');
    }
}
