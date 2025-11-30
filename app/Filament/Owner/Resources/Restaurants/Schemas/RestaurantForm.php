<?php

namespace App\Filament\Owner\Resources\Restaurants\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RestaurantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('General Information')
                    ->schema([
                        Select::make('owner_id')
                            ->label('Restaurant Owner')
                            ->relationship('owner', 'name') // relation with users table
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Restaurant Name')
                            ->required()
                            ->maxLength(255),

                        Textarea::make('description')
                            ->label('Description')
                            ->maxLength(500),
                    ]),

                Section::make('Images')
                    ->schema([
                        FileUpload::make('logo')
                            ->label('Logo')
                            ->image()
                            ->directory('restaurants/logos')
                            ->required(),

                        FileUpload::make('cover_image')
                            ->label('Cover Image')
                            ->image()
                            ->directory('restaurants/covers'),
                    ])->columns(2),

                Section::make('Contact Information')
                    ->schema([
                        TextInput::make('address')
                            ->label('Address')
                            ->required(),

                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(20),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                    ])->columns(3),

                Section::make('Location')
                    ->schema([
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->numeric(),


                        TextInput::make('longitude')
                            ->label('Longitude')
                            ->numeric(),

                    ])->columns(2),

                Section::make('Settings')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),


                        TextInput::make('commission_rate')
                            ->label('Commission Rate %')
                            ->numeric()
                            ->step(0.01)
                            ->default(10),
                    ])->columns(3),

                Section::make('Working Hours')
                    ->schema([
                        TimePicker::make('opening_time')
                            ->label('Opening Time')
                            ->required(),

                        TimePicker::make('closing_time')
                            ->label('Closing Time')
                            ->required(),
                    ])->columns(2),
            ]);
    }
}
