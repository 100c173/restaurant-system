<?php

namespace App\Filament\Clusters\FoodDatabase\Resources\Food\RelationManagers;

use App\Enums\FoodSourceStatus;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SourceRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'sourceRecords';

    protected static ?string $title = 'مصادر الغذاء';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('food_form_id')
                    ->label('الشكل')
                    ->relationship('foodForm', 'name_ar')
                    ->required()
                    ->searchable(),
                Select::make('data_source_id')
                    ->label('المصدر')
                    ->relationship('dataSource', 'name')
                    ->required()
                    ->searchable(),
                TextInput::make('external_ref')
                    ->label('الرقم المرجعي')
                    ->helperText('مثال: رقم العنصر في USDA')
                    ->maxLength(128),
                DateTimePicker::make('valid_from')->label('صالح من'),
                DateTimePicker::make('valid_to')->label('صالح حتى'),
                TextInput::make('notes')->label('ملاحظات')->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('foodForm.name_ar')
                    ->label('الشكل')
                    ->badge(),
                TextColumn::make('dataSource.name')
                    ->label('المصدر')
                    ->wrap(),
                TextColumn::make('external_ref')
                    ->label('الرقم المرجعي')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(FoodSourceStatus::class),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
