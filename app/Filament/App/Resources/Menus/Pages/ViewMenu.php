<?php

namespace App\Filament\App\Resources\Menus\Pages;

use App\Filament\App\Resources\Menus\MenuResource;
use App\Filament\App\Resources\Menus\RelationManagers\CategoriesRelationManager;
use App\Filament\App\Resources\Menus\Schemas\MenuForm;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class ViewMenu extends ViewRecord
{
    protected static string $resource = MenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
           // EditAction::make(),
        ];
    }
    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Menu information')
                    ->icon('heroicon-o-book-open')
                    ->columns(3)
                    ->headerActions([
                        // Edit button inside the card header — opens a modal
                        Action::make('edit_menu')
                            ->label('Edit')
                            ->icon('heroicon-o-pencil-square')
                            ->color('primary')
                            // Build the modal form from the shared MenuForm schema
                            ->form(fn(Schema $schema) => MenuForm::configure($schema))
                            // Pre-fill the modal with current record values
                            ->fillForm(fn(): array => $this->record->toArray())
                            // Save changes and refresh the infolist in place
                            ->action(function (array $data): void {
                                $this->record->update($data);

                                Notification::make()
                                    ->success()
                                    ->title('Menu updated')
                                    ->body('The menu information has been saved.')
                                    ->send();

                                // Refresh the page data so the infolist reflects changes
                                $this->refreshFormData([
                                    'name',
                                    'is_active',
                                    'position',
                                    'available_from',
                                    'available_until',
                                ]);
                            })
                            ->modalHeading('Edit menu')
                            ->modalWidth('2xl')
                            ->modalSubmitActionLabel('Save changes'),
                    ])
                    ->schema([
                        TextEntry::make('name')
                            ->label('Menu name')
                            ->weight('bold')
                            ->columnSpan(1),

                        TextEntry::make('position')
                            ->label('Display order')
                            ->badge()
                            ->color('gray')
                            ->columnSpan(1),

                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),

                        TextEntry::make('available_from')
                            ->label('Available from')
                            ->placeholder('All day')
                            ->time('H:i')
                            ->columnSpan(1),

                        TextEntry::make('available_until')
                            ->label('Available until')
                            ->placeholder('All day')
                            ->time('H:i')
                            ->columnSpan(1),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y, H:i')
                            ->columnSpan(1),
                    ]),
            ]);
    }


    public function getRelationManagers(): array
    {
        return [
            CategoriesRelationManager::class,
        ];
    }
}
