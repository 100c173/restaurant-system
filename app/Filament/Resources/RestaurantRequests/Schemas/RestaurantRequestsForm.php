<?php

namespace App\Filament\Resources\RestaurantRequests\Schemas;

use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RestaurantRequestsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('customer_id')
                    ->default(fn() => auth()->id()),

                TextInput::make('restaurant_name')
                    ->label('Restaurant Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('address')
                    ->required()
                    ->maxLength(255),

                TextInput::make('latitude')
                    ->numeric()
                    ->label('Latitude'),

                TextInput::make('longitude')
                    ->numeric()
                    ->label('Longitude'),

                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->disabled(),

                Textarea::make('cancel_reason')
                    ->label('Rejection Reason')
                    ->rows(3)
                    ->visible(fn($get) => $get('status') === 'rejected'),
            ]);
    }
}
