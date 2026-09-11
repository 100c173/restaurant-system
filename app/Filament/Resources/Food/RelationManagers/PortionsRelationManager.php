<?php

namespace App\Filament\Resources\Food\RelationManagers;

use App\Enums\ConfidenceLevel;
use App\Enums\PortionBasis;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PortionsRelationManager extends RelationManager
{
    protected static string $relationship = 'portions';

    protected static ?string $title = 'المقادير';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('measure_unit_id')
                ->label('وحدة القياس')
                ->relationship('measureUnit', 'name_ar')
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('amount')
                ->label('الكمية')
                ->numeric()
                ->default(1)
                ->required(),

            TextInput::make('gram_weight')
                ->label('الوزن بالغرام')
                ->numeric()
                ->required(),

            Select::make('food_form_id')
                ->label('الحالة')
                ->relationship('foodForm', 'name_ar')
                ->searchable()
                ->preload(),

            Select::make('basis')
                ->label('أساس التقدير')
                ->options(collect(PortionBasis::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name])),

            Select::make('confidence_level')
                ->label('مستوى الثقة')
                ->options(collect(ConfidenceLevel::cases())->mapWithKeys(fn ($c) => [$c->value => $c->name]))
                ->default(ConfidenceLevel::REFERENCE),

            Toggle::make('is_default')
                ->label('الوحدة الافتراضية؟'),

            DateTimePicker::make('valid_from')->label('صالح من'),
            DateTimePicker::make('valid_to')->label('صالح حتى'),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('gram_weight')
            ->columns([
                TextColumn::make('measureUnit.name_ar')->label('الوحدة'),
                TextColumn::make('amount')->label('الكمية'),
                TextColumn::make('gram_weight')->label('الوزن')->suffix(' غ'),
                TextColumn::make('foodForm.name_ar')->label('الحالة'),
                IconColumn::make('is_default')->label('افتراضي')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }
}
