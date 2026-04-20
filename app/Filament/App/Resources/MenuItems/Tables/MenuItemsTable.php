<?php

namespace App\Filament\App\Resources\MenuItems\Tables;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Grouping\Group;
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
use Modules\Restaurants\Models\Menu;

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

                TextColumn::make('category.menu.name')
                    ->label('Menu')
                    ->badge()
                    ->color('warning')
                    ->sortable()
                    ->placeholder('— No menu —'),

                TextColumn::make('price')
                    ->label('Price')
                    ->money('USD')
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
                // Replace both SelectFilters with a single custom Filter
                // that owns both form fields — this gives us full ->live() support
                Filter::make('menu_and_category')
                    ->form([
                        Select::make('menu_id')
                            ->label('Menu')
                            ->options(Menu::ordered()->pluck('name', 'id'))
                            ->placeholder('All Menus')
                            ->live()
                            ->afterStateUpdated(fn(callable $set) => $set('category_id', null)),

                        Select::make('category_id')
                            ->label('Category')
                            ->placeholder('All Categories')
                            ->searchable()
                            ->options(function (Get $get): array {
                                $menuId = $get('menu_id');

                                if (blank($menuId)) {
                                    return [];
                                }

                                return Category::query()
                                    ->where('menu_id', $menuId)
                                    ->ordered()
                                    ->pluck('name', 'id')
                                    ->toArray();
                            }),
                    ])
                    ->columns(2)
                    ->query(function (Builder $query, array $data): void {
                        $query
                            ->when(
                                filled($data['menu_id'] ?? null),
                                fn(Builder $q) => $q->whereHas(
                                    'category',
                                    fn(Builder $q) => $q->where('menu_id', $data['menu_id'])
                                )
                            )
                            ->when(
                                filled($data['category_id'] ?? null),
                                fn(Builder $q) => $q->where('category_id', $data['category_id'])
                            );
                    })
                    // Keep the filter indicator clean
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if (filled($data['menu_id'] ?? null)) {
                            $indicators[] = 'Menu: ' . Menu::find($data['menu_id'])?->name;
                        }

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
                Action::make('variants')
                    ->label('Variants')
                    ->icon('heroicon-o-squares-2x2')
                    ->url(fn($record) => MenuItemResource::getUrl('variants', ['record' => $record])),

                EditAction::make()
                    ->modalHeading('Edit Menu Item')
                    ->mutateFormDataUsing(function (array $data, $record): array {
                        $data['menu_id'] = $record->category?->menu_id;

                        return $data;
                    }),

                DeleteAction::make()
                    ->modalHeading('Delete Menu Item?')
                    ->modalDescription('Are you sure you want to delete this item? This action cannot be undone.')
                    ->modalSubmitActionLabel('Yes, delete item'),
            ])
            ->striped()
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateHeading('No menu items yet')
            ->emptyStateDescription('Add your first item to get started.');
    }
}