<?php

namespace App\Filament\Resources\FoodNutrients;

use App\Filament\Resources\FoodNutrients\Pages\CreateFoodNutrient;
use App\Filament\Resources\FoodNutrients\Pages\EditFoodNutrient;
use App\Filament\Resources\FoodNutrients\Pages\ListFoodNutrients;
use App\Models\Food;
use App\Models\FoodNutrient;
use App\Support\NutrientCatalog;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;

class FoodNutrientResource extends Resource
{
    protected static ?string $model = FoodNutrient::class;

    protected static string|UnitEnum|null $navigationGroup = 'Food Database';

    protected static ?string $navigationLabel = 'Food Nutrients';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('food_id')
                ->label('Food')
                ->relationship('food', 'name_ar')
                ->getOptionLabelFromRecordUsing(
                    fn (Food $record) => "{$record->name_ar} (FDC {$record->fdc_id})"
                )
                ->searchable(['name_ar', 'name_en'])
                ->preload()
                ->required(),

            Select::make('nutrient_id')
                ->label('Nutrient')
                ->options(NutrientCatalog::selectOptions())
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function (Set $set, ?string $state): void {
                    if ($state !== null) {
                        $set('nutrient_name', NutrientCatalog::nameFor((int) $state));
                        $set('unit', NutrientCatalog::unitFor((int) $state));
                    }
                }),

            TextInput::make('nutrient_name')
                ->label('Nutrient name')
                ->required()
                ->maxLength(255)
                ->helperText('Auto-filled from the nutrient chosen above; editable for custom nutrients.'),

            TextInput::make('unit')
                ->required()
                ->maxLength(10),

            TextInput::make('amount')
                ->numeric()
                ->step(0.0001)
                ->required()
                ->helperText('Per 100g, matching USDA convention.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('food.name_ar')
                    ->label('Food')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('food.fdc_id')
                    ->label('FDC ID')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('nutrient_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('amount')
                    ->numeric(decimalPlaces: 2)
                    ->sortable(),

                TextColumn::make('unit'),
            ])
            ->filters([
                SelectFilter::make('nutrient_id')
                    ->label('Nutrient')
                    ->options(NutrientCatalog::selectOptions()),

                SelectFilter::make('food_id')
                    ->label('Food')
                    ->relationship('food', 'name_ar')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('food_id');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with('food');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFoodNutrients::route('/'),
            'create' => CreateFoodNutrient::route('/create'),
            'edit' => EditFoodNutrient::route('/{record}/edit'),
        ];
    }
}
