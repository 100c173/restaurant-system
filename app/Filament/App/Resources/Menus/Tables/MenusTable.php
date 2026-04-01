<?php

namespace App\Filament\App\Resources\Menus\Tables;

use App\Filament\App\Resources\Menus\MenuResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('position')
                    ->label('#')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->width('60px'),

                TextColumn::make('name')
                    ->label('Menu name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->description('Click to open'),

                TextColumn::make('categories_count')
                    ->label('Categories')
                    ->counts('categories')
                    ->badge()
                    ->color(fn(int $state): string => $state > 0 ? 'info' : 'gray')
                    ->formatStateUsing(
                        fn(int $state): string => $state > 0
                        ? "{$state} " . str('category')->plural($state)
                        : 'No categories'
                    ),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn(bool $state): string => $state ? 'Active' : 'Inactive'),

                TextColumn::make('available_from')
                    ->label('Available from')
                    ->time('H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('available_until')
                    ->label('Until')
                    ->time('H:i')
                    ->placeholder('—')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Last updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->defaultSort('position', 'asc')

            // Every row click opens the view page
            ->recordUrl(
                fn($record): string => MenuResource::getUrl('view', ['record' => $record])
            )

            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All menus')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])

            ->actions([
                // View action mirrors the row click
                ViewAction::make()
                    ->iconButton()
                    ->tooltip('Open menu'),

                // Delete stays as a direct action on the list
                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete menu')
                    ->requiresConfirmation()
                    ->modalHeading('Delete menu')
                    ->modalDescription('Are you sure? All categories inside this menu will be affected.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])

            ->bulkActions([
                DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])

            ->emptyStateIcon('heroicon-o-book-open')
            ->emptyStateHeading('No menus yet')
            ->emptyStateDescription('Create your first menu to start organising your categories and items.')
            ->striped();
    }
}