<?php

namespace App\Filament\App\Resources\Categories\Pages;

use App\Filament\App\Resources\Categories\CategoryResource;
use App\Filament\App\Resources\Categories\Schemas\CategoryForm;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewCategories extends ViewRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //EditAction::make(),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Category information')
                    ->icon('heroicon-o-tag')
                    ->columns(3)
                    ->headerActions([
                        // Edit button inside the card — opens a modal
                        Action::make('edit_category')
                            ->label('Edit')
                            ->icon('heroicon-o-pencil-square')
                            ->color('primary')
                            ->form(fn(Schema $schema) => CategoryForm::configure($schema))
                            ->fillForm(fn(): array => $this->record->toArray())
                            ->action(function (array $data): void {
                                $this->record->update($data);

                                Notification::make()
                                    ->success()
                                    ->title('Category updated')
                                    ->body('The category information has been saved.')
                                    ->send();

                                $this->refreshFormData([
                                    'name',
                                    'description',
                                    'is_active',
                                    'position',
                                    'menu_id',
                                    'img_path',
                                ]);
                            })
                            ->modalHeading('Edit category')
                            ->modalWidth('2xl')
                            ->modalSubmitActionLabel('Save changes'),
                    ])
                    ->schema([
                        TextEntry::make('name')
                            ->label('Category name')
                            ->weight('bold')
                            ->columnSpan(1),

                        TextEntry::make('menu.name')
                            ->label('Menu')
                            ->badge()
                            ->color('info')
                            ->placeholder('No menu assigned')
                            ->columnSpan(1),

                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger')
                            ->columnSpan(1),

                        TextEntry::make('description')
                            ->label('Description')
                            ->placeholder('—')
                            ->columnSpan(2),

                        TextEntry::make('position')
                            ->label('Display order')
                            ->badge()
                            ->color('gray')
                            ->columnSpan(1),

                        ImageEntry::make('img_path')
                            ->label('Image')
                            ->disk('tenant_uploads')
                            ->visibility('public')
                            ->circular()
                            ->columnSpan(1),

                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('d M Y, H:i')
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
