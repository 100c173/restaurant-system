<?php

namespace App\Filament\Owner\Resources\MenuItems\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Hidden::make('restaurant_id')
                    ->default(fn() => auth()->user()->restaurant->id),

                Section::make('Product Information')
                    ->description('Basic details about your product')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Product Name')
                            ->required()
                            ->maxLength(150)
                            ->minLength(3)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Enter product name')
                            ->prefixIcon('heroicon-o-shopping-bag'),

                        Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(4)
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Write a short description')

                    ]),

                Section::make('Pricing & Availability')
                    ->description('Set the price and availability status')
                    ->columns(2)
                    ->schema([
                        TextInput::make('price')
                            ->label('Price')
                            ->required()
                            ->numeric()
                            ->minValue(0.0)
                            ->maxValue(10000)
                            ->prefix('$')
                            ->placeholder('Enter product price')
                            ->prefixIcon('heroicon-o-currency-dollar'),

                        Toggle::make('is_available')
                            ->label('Available for Sale?')
                            ->required()
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('Media')
                    ->description('Upload product images')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Product Image')
                            ->image()
                            ->directory('products')
                            ->maxSize(2048) // 2MB max
                            ->imageEditor()
                            ->required()
                            ->downloadable()
                            ->openable()
                            ->uploadingMessage('Uploading image...')
                            ->panelAspectRatio('16:9')
                            ->panelLayout('integrated')
                            ->helperText('Max size: 2MB')

                    ]),
            ]);
    }
}
