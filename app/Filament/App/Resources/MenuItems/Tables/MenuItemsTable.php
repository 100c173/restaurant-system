<?php

namespace App\Filament\App\Resources\MenuItems\Tables;

use App\Filament\App\Resources\Categories\RelationManagers\MenuItemsRelationManager;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([

                // Position badge
                TextColumn::make('position')
                    ->label('#')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->width('56px'),

                // Item image
                ImageColumn::make('image')
                    ->label('Photo')
                    ->disk('tenant_uploads')
                    ->url(
                        fn($record) => $record->img_path
                        ? url('/tenant-image/' . tenant('id') . '/' . $record->img_path)
                        : null
                    )
                    ->visibility('public')
                    ->width(48)
                    ->circular(),

                // Item name + description
                TextColumn::make('name')
                    ->label('Item')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->description(
                        fn($record): ?string => $record->description
                        ? str($record->description)->limit(55)
                        : null
                    ),

                // Category — hidden inside the relation manager (already shown as context)
                TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->hiddenOn(MenuItemsRelationManager::class),

                // Price
                TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
                    ->sortable(),

                // Preparation time
                TextColumn::make('preparation_time')
                    ->label('Prep time')
                    ->formatStateUsing(
                        fn(?int $state): string => $state ? "{$state} min" : '—'
                    )
                    ->sortable(),

                // Featured flag
                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->tooltip(fn(bool $state): string => $state ? 'Featured' : 'Not featured'),

                // Available status
                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn(bool $state): string => $state ? 'Available' : 'Unavailable'),

                // Timestamps (hidden by default)
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

            ->filters([
                // Filter by category — hidden inside relation manager
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable()
                    ->preload()
                    ->hiddenOn(MenuItemsRelationManager::class),

                // Filter by availability
                TernaryFilter::make('is_available')
                    ->label('Availability')
                    ->placeholder('All items')
                    ->trueLabel('Available only')
                    ->falseLabel('Unavailable only'),

                // Filter by featured
                TernaryFilter::make('is_featured')
                    ->label('Featured')
                    ->placeholder('All items')
                    ->trueLabel('Featured only')
                    ->falseLabel('Not featured'),
            ])

            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit item'),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete item')
                    ->requiresConfirmation()
                    ->modalHeading('Delete menu item')
                    ->modalDescription('Are you sure you want to delete this item? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])

            ->bulkActions([
                DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])

            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No items yet')
            ->emptyStateDescription('Add your first menu item to this category.')
            ->striped();
    }
}
