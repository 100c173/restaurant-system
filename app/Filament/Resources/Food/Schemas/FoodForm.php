<?php
namespace App\Filament\Resources\Food\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class FoodForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([

                TextInput::make('name_ar')
                    ->label('الاسم بالعربي')
                    ->required()
                    ->maxLength(255),

                TextInput::make('name_en')
                    ->label('الاسم بالإنكليزي')
                    ->required()
                    ->maxLength(255),

                Select::make('category_id')
                    ->label('التصنيف')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                Toggle::make('is_active')
                    ->label('فعال')
                    ->default(true),
            ]);
    }
}
