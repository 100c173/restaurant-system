<?php

namespace App\Filament\Resources\Recipes\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class IngredientsRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredients';

    protected static ?string $title = 'المكونات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('food_id')
                ->label('المكوّن (الغذاء)')
                // an ingredient can't itself be a dish that has its own recipe
                ->relationship('food', 'name_ar', fn ($query) => $query->whereDoesntHave('recipe'))
                ->searchable()
                ->preload()
                ->required(),

            Select::make('food_form_id')
                ->label('حالة المكوّن')
                ->relationship('foodForm', 'name_ar')
                ->searchable()
                ->preload(),

            Select::make('measure_unit_id')
                ->label('وحدة القياس')
                ->relationship('measureUnit', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('amount')
                ->label('الكمية')
                ->numeric()
                ->required(),

            Toggle::make('is_added_after_cooking')
                ->label('يُضاف بعد الطهي؟'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('food.name_ar')
            ->columns([
                TextColumn::make('food.name_ar')->label('المكوّن'),
                TextColumn::make('foodForm.name_ar')->label('الحالة'),
                TextColumn::make('amount')->label('الكمية'),
                TextColumn::make('measureUnit.name_ar')->label('الوحدة'),
                IconColumn::make('is_added_after_cooking')->label('بعد الطهي')->boolean(),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
