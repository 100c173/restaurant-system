<?php

namespace App\Filament\Resources\Recipes\Schemas;


use App\Enums\RecipeStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات الوصفة')
                ->columns(2)
                ->schema([
                    Select::make('food_id')
                        ->label('الطبق (الغذاء المرتبط)')
                        ->relationship('food', 'name_ar')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->default(fn () => request()->query('food_id'))
                        ->disabledOn('edit'), // the food↔recipe link shouldn't change after creation

                    TextInput::make('servings')
                        ->label('عدد الحصص')
                        ->numeric()
                        ->default(1)
                        ->required(),

                    TextInput::make('weight_before_cooking_g')
                        ->label('الوزن قبل الطهي (غ)')
                        ->numeric(),

                    TextInput::make('weight_after_cooking_g')
                        ->label('الوزن بعد الطهي (غ)')
                        ->numeric(),

                    Select::make('status')
                        ->label('الحالة')
                        ->options(collect(RecipeStatus::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name]))
                        ->default(RecipeStatus::DRAFT)
                        ->required(),
                ]),

            Textarea::make('notes')
                ->label('ملاحظات')
                ->columnSpanFull(),
        ]);
    }
}
