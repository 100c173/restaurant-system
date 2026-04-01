<?php

namespace App\Filament\App\Resources\Menus\Pages;

use App\Filament\App\Resources\Menus\MenuResource;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    // Header actions — delete button next to the form title
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->requiresConfirmation()
                ->modalHeading('Delete menu')
                ->modalDescription('Are you sure? All categories and items inside this menu will be affected.')
                ->modalSubmitActionLabel('Yes, delete it'),
        ];
    }

    // Redirect back to list after saving
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    // Success notification
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Menu updated')
            ->body('Your changes have been saved successfully.');
    }
}

