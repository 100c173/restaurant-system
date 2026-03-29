<?php

namespace App\Filament\App\Resources\Menus\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([

            Section::make('General information')
                ->description('The name and status of this menu.')
                ->icon('heroicon-o-book-open')
                ->schema([
                    Grid::make(2)->schema([

                        TextInput::make('name')
                            ->label('Menu name')
                            ->placeholder('e.g. Lunch Menu, Ramadan Special…')
                            ->required()
                            ->maxLength(100)
                            ->columnSpan(1),

                        TextInput::make('position')
                            ->label('Display order')
                            ->helperText('Lower numbers appear first in the app.')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->columnSpan(1),
                    ]),

                    Toggle::make('is_active')
                        ->label('Active')
                        ->helperText('Inactive menus are hidden from customers immediately.')
                        ->default(true)
                        ->inline(false),
                ]),

            Section::make('Time availability')
                ->description('Restrict this menu to specific hours. Leave both fields empty to make it available all day.')
                ->icon('heroicon-o-clock')
                ->schema([
                    Grid::make(2)->schema([

                        TimePicker::make('available_from')
                            ->label('Available from')
                            ->placeholder('e.g. 11:00')
                            ->seconds(false)
                            ->nullable()
                            ->columnSpan(1),

                        TimePicker::make('available_until')
                            ->label('Available until')
                            ->placeholder('e.g. 17:00')
                            ->seconds(false)
                            ->nullable()
                            ->columnSpan(1)
                            ->afterOrEqual('available_from')
                            ->validationMessages([
                                'after_or_equal' => 'The end time must be after the start time.',
                            ]),
                    ]),
                ])
                ->collapsible(),

        ]);
    }
}