<?php

namespace App\Filament\App\Resources\Menus\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {

        return $schema
            ->components([
                Section::make('Menu Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Menu Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),

                        TextInput::make('position')
                            ->label('Display Position')
                            ->numeric()
                            ->default(0),
                    ]),

                Section::make('Time Availability')
                    ->description('Optionally restrict when this menu is available.')
                    ->columns(2)
                    ->schema([
                        TimePicker::make('available_from')
                            ->label('Available From')
                            ->seconds(false)
                            ->nullable(),

                        TimePicker::make('available_until')
                            ->label('Available Until')
                            ->seconds(false)
                            ->nullable(),
                    ]),
            ]);
    }
}
