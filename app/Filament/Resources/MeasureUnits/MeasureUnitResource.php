<?php

namespace App\Filament\Resources\MeasureUnits;

use App\Filament\Resources\MeasureUnits\Pages\CreateMeasureUnit;
use App\Filament\Resources\MeasureUnits\Pages\EditMeasureUnit;
use App\Filament\Resources\MeasureUnits\Pages\ListMeasureUnits;
use App\Models\MeasureUnit;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class MeasureUnitResource extends Resource
{
    protected static ?string $model = MeasureUnit::class;

    protected static string|UnitEnum|null $navigationGroup = 'Food Database';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $navigationLabel = 'Measure Units';

    protected static ?string $recordTitleAttribute = 'name_en';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Measure unit')
                ->columns(2)
                ->components([
                    TextInput::make('usda_id')
                        ->label('USDA ID')
                        ->numeric()
                        ->unique(ignoreRecord: true)
                        ->helperText('Leave blank for tenant/local-only units (e.g. "رغيف").'),

                    TextInput::make('name_en')
                        ->label('English name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('name_ar')
                        ->label('Arabic name')
                        ->required()
                        ->maxLength(255),

                    TextInput::make('notes')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('usda_id')
                    ->label('USDA ID')
                    ->sortable()
                    ->searchable()
                    ->placeholder('— local —')
                    ->copyable(),

                TextColumn::make('name_en')
                    ->label('English name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('name_ar')
                    ->label('Arabic name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('notes')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name_en');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeasureUnits::route('/'),
            'create' => CreateMeasureUnit::route('/create'),
            'edit' => EditMeasureUnit::route('/{record}/edit'),
        ];
    }
}