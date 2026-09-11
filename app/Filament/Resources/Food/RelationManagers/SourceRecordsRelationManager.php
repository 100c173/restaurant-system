<?php

namespace App\Filament\Resources\Food\RelationManagers;

use App\Filament\Resources\Food\Resources\FoodSourceRecords\FoodSourceRecordResource;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SourceRecordsRelationManager extends RelationManager
{
    protected static string $relationship = 'sourceRecords';

    protected static ?string $relatedResource = FoodSourceRecordResource::class;

    protected static ?string $title = 'مصادر البيانات';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('source_name')
            ->columns([
                TextColumn::make('source_type')->label('نوع المصدر')->badge(),
                TextColumn::make('external_ref')->label('المرجع'),
                TextColumn::make('foodForm.name_ar')->label('الحالة'),
                TextColumn::make('status')->label('حالة السجل')->badge(),
                TextColumn::make('priority')->label('الأولوية'),
                IconColumn::make('is_preferred')->label('مفضل')->boolean(),
            ])
            ->defaultSort('priority')
            // Because $relatedResource is set above, these actions automatically
            // navigate to FoodSourceRecordResource's create/edit pages
            // (full page) instead of opening a modal.
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
