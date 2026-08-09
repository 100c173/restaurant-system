<?php

namespace App\Filament\Resources\FoodPortions;

use App\Filament\Resources\FoodPortions\Pages\CreateFoodPortion;
use App\Filament\Resources\FoodPortions\Pages\EditFoodPortion;
use App\Filament\Resources\FoodPortions\Pages\ListFoodPortions;
use App\Models\FoodPortion;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class FoodPortionResource extends Resource
{
    protected static ?string $model = FoodPortion::class;

    protected static string|UnitEnum|null $navigationGroup = 'Food Database';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?string $navigationLabel = 'Food Portions';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Portion')
                ->columns(2)
                ->components([
                    Select::make('food_id')
                        ->label('Food')
                        ->relationship('food', 'name_ar')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name_ar} (FDC {$record->fdc_id})")
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('measure_unit_id')
                        ->label('Measure unit')
                        ->relationship('measureUnit', 'name_en')
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name_en} / {$record->name_ar}")
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('amount')
                        ->numeric()
                        ->default(1)
                        ->required()
                        ->helperText('e.g. 1, 0.5, 2'),

                    TextInput::make('modifier')
                        ->helperText('e.g. "cooked", "large", "chopped", "raw"'),

                    TextInput::make('gram_weight')
                        ->label('Gram weight')
                        ->numeric()
                        ->required()
                        ->suffix('g')
                        ->helperText('Resolved weight in grams for `amount` of this unit.'),

                    TextInput::make('data_points')
                        ->label('Data points')
                        ->numeric()
                        ->default(0),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('food_id')
                    ->label('ID'),
                    
                TextColumn::make('food.name_ar')
                    ->label('Food')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('food.fdc_id')
                    ->label('FDC ID')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('measureUnit.name_en')
                    ->label('Unit')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->numeric(3),

                TextColumn::make('modifier')
                    ->toggleable(),

                TextColumn::make('gram_weight')
                    ->label('Grams')
                    ->numeric(3)
                    ->sortable(),

                TextColumn::make('data_points')
                    ->label('Data points')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('food_id');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with(['food', 'measureUnit']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFoodPortions::route('/'),
            'create' => CreateFoodPortion::route('/create'),
            'edit' => EditFoodPortion::route('/{record}/edit'),
        ];
    }
}