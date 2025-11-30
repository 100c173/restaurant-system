<?php

namespace App\Filament\Resources\Restaurants\Schemas;


use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Modules\Restaurants\Models\Restaurant;

class RestaurantsForm
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


                        Select::make('status')
                            ->label('Status')
                            ->options(options: [
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->default('pending')
                            ->disabled(fn($record) => $record && in_array($record->status, ['approved', 'rejected']))
                            ->afterStateUpdated(function (?string $state, Restaurant $record) {
                                if ($record && in_array($state, ['approved', 'rejected'])) {
                                    $owner = $record->owner;
                                    if ($owner) {
                                        Notification::make()
                                            ->title('Your request status has been updated')
                                            ->body('Your restaurant status has been updated to:'. $state)
                                            ->sendToDatabase($owner);
                                    }
                                }
                            }),


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
