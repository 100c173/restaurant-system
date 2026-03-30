<?php

namespace App\Filament\App\Resources\MenuItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Modules\Restaurants\Models\Category;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            // Single column layout with full-width fields
            Grid::make(1)->schema([

                Section::make('Item Image')
                    ->description('Upload a photo of this menu item')
                    ->icon('heroicon-o-photo')
                    ->collapsible()
                    ->schema([
                        FileUpload::make('image')
                            ->label(false)
                            ->image()
                            ->disk('tenant_uploads')
                            ->imageEditor()
                            ->imageEditorAspectRatios(['1:1', '4:3', '16:9'])
                            ->directory('menu-items')
                            ->visibility('public')
                            ->maxSize(3072)
                            ->helperText('Max 3 MB. Square (1:1) recommended.')
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('General Information')
                    ->description('Core details of this menu item')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextInput::make('name')
                            ->label('Item name')
                            ->placeholder('e.g. Classic Smash Burger…')
                            ->required()
                            ->maxLength(150)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Description')
                            ->placeholder('Describe the item — ingredients, taste, highlights…')
                            ->rows(4)
                            ->maxLength(1000)
                            ->nullable()
                            ->columnSpanFull(),

                        Select::make('category_id')
                            ->label('Category')
                            ->placeholder('Select a category…')
                            ->options(
                                fn() => Category::active()
                                    ->with('menu')
                                    ->ordered()
                                    ->get()
                                    ->groupBy(fn($c) => $c->menu?->name ?? 'No menu')
                                    ->map(fn($group) => $group->pluck('name', 'id'))
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Categories are grouped by their parent menu.')
                            ->columnSpanFull(),
                    ]),

                Section::make('Pricing & Logistics')
                    ->description('Price, preparation time, and display order')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('price')
                                ->label('Base price')
                                ->numeric()
                                ->prefix('$')
                                ->minValue(0)
                                ->step(0.01)
                                ->default(0)
                                ->required()
                                ->extraAttributes(['class' => 'text-xl py-3']),

                            TextInput::make('preparation_time')
                                ->label('Preparation time')
                                ->numeric()
                                ->suffix('min')
                                ->minValue(1)
                                ->nullable()
                                ->helperText('Shown to customers in the app.')
                                ->extraAttributes(['class' => 'text-xl py-3']),

                            TextInput::make('position')
                                ->label('Display order')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->helperText('Lower numbers appear first.')
                                ->extraAttributes(['class' => 'text-xl py-3']),
                        ])->columns(2),
                    ]),

                Section::make('Visibility & Flags')
                    ->description('Control how this item appears to customers')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Grid::make(2)->schema([
                            Toggle::make('is_available')
                                ->label('Available')
                                ->helperText('Unavailable items are hidden from customers immediately.')
                                ->default(true)
                                ->inline(false),

                            Toggle::make('is_featured')
                                ->label('Featured')
                                ->helperText('Featured items are pinned and highlighted in the app.')
                                ->default(false)
                                ->inline(false),
                        ]),
                    ]),

            ])->columnSpanFull(),

        ]);
    }
}