<?php

namespace App\Filament\Resources\Foods;

use App\Filament\Resources\Foods\Pages\CreateFood;
use App\Filament\Resources\Foods\Pages\EditFood;
use App\Filament\Resources\Foods\Pages\ListFoods;
use App\Models\Food;
use BackedEnum;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class FoodResource extends Resource
{
    protected static ?string $model = Food::class;

    protected static string|UnitEnum|null $navigationGroup = 'Food Database';

    protected static string|BackedEnum|null $navigationIcon =Heroicon::OutlinedRocketLaunch;

    protected static ?string $navigationLabel = 'Foods';

    protected static ?string $recordTitleAttribute = 'name_ar';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('USDA reference')
                ->description('The USDA FoodData Central identity of this ingredient.')
                ->columns(2)
                ->components([
                    TextInput::make('fdc_id')
                        ->label('FDC ID')
                        ->numeric()
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('The unique fdcId from USDA FoodData Central.'),

                    TextInput::make('data_type')
                        ->label('Data type')
                        ->helperText('e.g. Foundation, SR Legacy, Branded'),
                ]),

            Section::make('Ingredient details')
                ->columns(2)
                ->components([
                    TextInput::make('name_ar')
                        ->label('Arabic name')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    TextInput::make('name_en')
                        ->label('English query / name'),

                    Select::make('category')
                        ->options([
                            'Grains' => 'Grains',
                            'Legumes' => 'Legumes',
                            'Vegetables' => 'Vegetables',
                            'Herbs' => 'Herbs',
                            'Spices' => 'Spices',
                            'Dairy' => 'Dairy',
                            'Meat' => 'Meat',
                            'Fruit' => 'Fruit',
                            'Nuts/Seeds' => 'Nuts/Seeds',
                            'Oils' => 'Oils',
                            'Sweeteners' => 'Sweeteners',
                            'Prepared' => 'Prepared',
                        ])
                        ->searchable(),

                    Textarea::make('description')
                        ->label('USDA description')
                        ->columnSpanFull()
                        ->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fdc_id')
                    ->label('FDC ID')
                    ->sortable()
                    ->searchable()
                    ->copyable(),

                TextColumn::make('name_ar')
                    ->label('Arabic name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_en')
                    ->label('English name')
                    ->searchable()
                    ->toggleable(),

               // TextColumn::make('category')
                //    ->badge()
                //    ->sortable(),

                TextColumn::make('data_type')
                    ->label('Data type')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('nutrients_count')
                    ->label('Nutrients')
                    ->counts('nutrients')
                    ->badge(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'Grains' => 'Grains',
                        'Legumes' => 'Legumes',
                        'Vegetables' => 'Vegetables',
                        'Herbs' => 'Herbs',
                        'Spices' => 'Spices',
                        'Dairy' => 'Dairy',
                        'Meat' => 'Meat',
                        'Fruit' => 'Fruit',
                        'Nuts/Seeds' => 'Nuts/Seeds',
                        'Oils' => 'Oils',
                        'Sweeteners' => 'Sweeteners',
                        'Prepared' => 'Prepared',
                    ]),
            ])
            ->defaultSort('name_ar');
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->withCount('nutrients');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFoods::route('/'),
            'create' => CreateFood::route('/create'),
            'edit' => EditFood::route('/{record}/edit'),
        ];
    }
}
