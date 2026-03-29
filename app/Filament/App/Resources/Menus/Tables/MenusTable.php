<?php

namespace App\Filament\App\Resources\Menus\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
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

                // Position badge — makes drag-order visible at a glance
                TextColumn::make('position')
                    ->label('#')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->width('60px'),

                // Menu name — primary identifier
                TextColumn::make('name')
                    ->label('Menu name')
                    ->sortable()
                    ->searchable()
                    ->weight('medium'),

                // Active / inactive status
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->tooltip(fn (bool $state): string => $state ? 'Active' : 'Inactive'),

                // Availability window — shown as a readable time range
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

                // Timestamps
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
                TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('All menus')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])

            ->actions([
                EditAction::make()
                    ->iconButton()
                    ->tooltip('Edit menu'),

                DeleteAction::make()
                    ->iconButton()
                    ->tooltip('Delete menu')
                    ->requiresConfirmation()
                    ->modalHeading('Delete menu')
                    ->modalDescription('Are you sure you want to delete this menu? All categories and items inside it will be affected.')
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