<?php

namespace App\Filament\App\Resources\Menus\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Menu Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold'),

                TextColumn::make('position')
                    ->label('Position')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('available_from')
                    ->label('Available From')
                    ->placeholder('Any time')
                    ->alignCenter(),

                TextColumn::make('available_until')
                    ->label('Available Until')
                    ->placeholder('Any time')
                    ->alignCenter(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),

                TextColumn::make('categories_count')
                    ->label('Categories')
                    ->counts('categories')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
            ])

            ->recordActions([
                // 1. Categories Icon Action — opens modal with infolist
                Action::make('viewCategories')
                    ->label('Categories')
                    ->icon('heroicon-o-tag')
                    ->color('info')
                    ->modalHeading(fn($record) => "Categories for \"{$record->name}\"")
                    ->modalContent(function ($record): \Illuminate\Contracts\View\View {
                        return view('filament.modals.categories-modal', [
                            'categories' => $record->categories()->ordered()->get(),
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                // 2. Edit Action — opens edit form in modal
                EditAction::make()
                    ->label('Edit Menu')
                    ->icon('heroicon-o-pencil-square')
                    ->modalHeading('Edit Menu'),

                // 3. Delete Action — with cascade warning
                DeleteAction::make()
                    ->modalHeading('Delete Menu?')
                    ->modalDescription(
                        'Are you sure you want to delete this menu? ' .
                        'This will also permanently delete all categories associated with it.'
                    )
                    ->modalSubmitActionLabel('Yes, delete menu')
                    ->color('danger'),
            ])

            ->toolbarActions([
                CreateAction::make()
                    ->label('New Menu')
                    ->icon('heroicon-o-plus'),
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])

            ->defaultSort('position', 'asc')
            ->striped();
    }
}
