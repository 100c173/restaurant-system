<?php

namespace App\Filament\App\Resources\Categories\Tables;

use App\Filament\App\Resources\Menus\RelationManagers\CategoriesRelationManager;
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

class CategoriesTable
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
                    ->width('60px'),

                // Category image thumbnail
                ImageColumn::make('img_path')
                    ->label('Image')
                    ->disk('tenant_uploads')
                    ->url(
                        fn($record) => $record->img_path
                        ? url('/tenant-image/' . tenant('id') . '/' . $record->img_path)
                        : null
                    )
                    ->circular()
                    ->visibility('public')
                    ->width(40),

                // Category name
                TextColumn::make('name')
                    ->label('Category')
                    ->sortable()
                    ->searchable()
                    ->weight('medium')
                    ->description(
                        fn($record): ?string => $record->description
                        ? str($record->description)->limit(60)
                        : null
                    ),

                // Menu this category belongs to
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('info')
                    ->placeholder('— No menu —')
                    // Hide this column when inside the relation manager
                    // (the parent menu is already shown above the table)
                    ->hiddenOn(CategoriesRelationManager::class),

                // Active status
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn(bool $state): string => $state ? 'Active' : 'Inactive'),

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
                // Filter by menu
                SelectFilter::make('menu_id')
                    ->label('Menu')
                    ->relationship('menu', 'name')
                    ->searchable()
                    ->preload()
                    ->hiddenOn(CategoriesRelationManager::class),

                // Filter by active status
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All categories')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])

            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit category'),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete category')
                    ->requiresConfirmation()
                    ->modalHeading('Delete category')
                    ->modalDescription('Are you sure? Items inside this category may be affected.')
                    ->modalSubmitActionLabel('Yes, delete it'),
            ])

            ->bulkActions([
                DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ])

            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Add your first category to start organising your menu items.')
            ->striped();
    }
}
