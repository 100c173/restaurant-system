<?php

namespace App\Filament\App\Resources\Categories\Tables;

use App\Filament\App\Resources\Menus\MenuResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // Thumbnail
                ImageColumn::make('image')
                    ->label('')
                    ->circular(),

                // Name
                TextColumn::make('name')
                    ->label('Category Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                // Description — truncated
                TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->placeholder('—')
                    ->toggleable(),

                // Items count
                TextColumn::make('menu_items_count')
                    ->label('Items')
                    ->counts('menuItems')
                    ->badge()
                    ->color('gray')
                    ->alignCenter(),

                // Position
                TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->alignCenter()
                    ->toggleable(),

                // Status
                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
            ])

            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])

            ->recordActions([
                // Edit — opens modal
                EditAction::make()
                    ->label('Edit')
                    ->modalHeading('Edit Category'),

                // Delete — with cascade warning
                DeleteAction::make()
                    ->modalHeading('Delete Category?')
                    ->modalDescription(
                        'Are you sure you want to delete this category? ' .
                        'This will also permanently delete all menu items associated with it.'
                    )
                    ->modalSubmitActionLabel('Yes, delete category'),
            ])

            ->toolbarActions([
                CreateAction::make()
                    ->label('New Category')
                    ->icon('heroicon-o-plus'),
                    
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('position', 'asc')
            ->striped()
            ->emptyStateIcon('heroicon-o-tag')
            ->emptyStateHeading('No categories yet')
            ->emptyStateDescription('Create your first category to get started.');
    }
}
