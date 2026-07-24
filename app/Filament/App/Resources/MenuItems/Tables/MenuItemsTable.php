<?php

namespace App\Filament\App\Resources\MenuItems\Tables;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurants\Models\Category;

class MenuItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('')
                    ->circular(),

                TextColumn::make('name')
                    ->label('Item Name')
                    ->searchable()
                    ->sortable()
                    ->weight('semibold')
                    ->description(
                        fn($record): ?string => $record->description
                        ? str($record->description)->limit(60)
                        : null
                    ),

                TextColumn::make('category.name')
                    ->label('Category')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('SYP')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('preparation_time')
                    ->label('Prep')
                    ->suffix(' min')
                    ->placeholder('—')
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('is_featured')
                    ->label('Featured')
                    ->boolean()
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter()
                    ->toggleable(),

                IconColumn::make('is_available')
                    ->label('Available')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
            ])

            ->filters([
                Filter::make('category')
                    ->form([
                        Select::make('category_id')
                            ->label('Category')
                            ->placeholder('All Categories')
                            ->searchable()
                            ->options(
                                fn() => Category::query()
                                    ->active()
                                    ->ordered()
                                    ->pluck('name', 'id')
                                    ->toArray()
                            ),
                    ])
                    ->query(function (Builder $query, array $data): void {
                        $query->when(
                            filled($data['category_id'] ?? null),
                            fn(Builder $q) => $q->where('category_id', $data['category_id'])
                        );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['category_id'] ?? null)) {
                            $indicators[] = 'Category: ' . Category::find($data['category_id'])?->name;
                        }

                        return $indicators;
                    }),

                SelectFilter::make('is_available')
                    ->label('Availability')
                    ->options([
                        '1' => 'Available',
                        '0' => 'Unavailable',
                    ]),

                SelectFilter::make('is_featured')
                    ->label('Featured')
                    ->options([
                        '1' => 'Featured',
                        '0' => 'Not Featured',
                    ]),
            ])
            ->filtersLayout(FiltersLayout::AboveContent)
            ->filtersFormColumns(3)

            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Menu Item')
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        $data['menu_id'] = $record->category?->menu_id;

                        return $data;
                    }),

                ActionGroup::make([

                    Action::make('variants')
                        ->label('Variants')
                        ->icon('heroicon-o-squares-2x2')
                        ->url(fn($record) => MenuItemResource::getUrl('variants', ['record' => $record])),

                    Action::make('modifiers')
                        ->label('Modifiers')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->url(fn($record) => MenuItemResource::getUrl('modifiers', ['record' => $record])),
                        
                    Action::make('ingredients')
                        ->label('Ingredients')
                        ->icon('heroicon-o-beaker')
                        ->url(fn($record) => MenuItemResource::getUrl('ingredients', ['record' => $record])),

                    DeleteAction::make()
                        ->modalHeading('Delete Menu Item?')
                        ->modalDescription('Are you sure you want to delete this item? This action cannot be undone.')
                        ->modalSubmitActionLabel('Yes, delete item'),
                ])
                    ->label('More')
                    ->button()
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->color('gray'),
            ])
            ->striped()
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No menu items yet')
            ->emptyStateDescription('Add your first item to get started.');
    }
}