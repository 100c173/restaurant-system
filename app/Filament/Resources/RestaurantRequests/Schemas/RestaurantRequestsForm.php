<?php

namespace App\Filament\Resources\RestaurantRequests\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RestaurantRequestsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('owner_name')
                    ->required(),

                TextInput::make('owner_email')
                    ->email()
                    ->required(),

                TextInput::make('owner_phone')
                    ->required(),

                TextInput::make('restaurant_name')
                    ->required(),

                TextInput::make('restaurant_email')
                    ->email()
                    ->required(),

                TextInput::make('restaurant_phone')
                    ->required(),

                Textarea::make('address')
                    ->required(),
            ]);
    }
}
