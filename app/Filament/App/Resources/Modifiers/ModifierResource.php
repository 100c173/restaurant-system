<?php

namespace App\Filament\App\Resources\Modifiers;

use App\Filament\App\Resources\Modifiers\Pages\ManageModifiers;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Modules\Restaurants\Models\Modifier;
use UnitEnum;

class ModifierResource extends Resource
{
    protected static ?string $model = Modifier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static string|UnitEnum|null $navigationGroup = 'Modifier Group';
    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Modifier Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Modifier Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g. Extra cheese, No onions'),

                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->default(0)
                            ->prefix('$')
                            ->minValue(0)
                            ->step(0.01)
                            ->helperText('Set 0 for free modifiers'),

                        Toggle::make('is_available')
                            ->label('Available')
                            ->default(true),

                        TextInput::make('position')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Lower value appears first'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                IconColumn::make('is_available')
                    ->boolean()
                    ->label('Available'),

                TextColumn::make('position')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_available')
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageModifiers::route('/'),
        ];
    }
}
