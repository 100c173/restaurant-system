<?php
namespace App\Filament\Resources\Food\Resources\FoodSourceRecords\Tables;

use App\Enums\Country;
use App\Enums\FoodSourceStatus;
use App\Enums\FoodSourceType;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FoodSourceRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                /*ImageColumn::make('img')
                    ->label('الغذاء')
                    ->getStateUsing(function ($record) {
                        return asset('storage/'. $record->img);
                    })->imageHeight(70)
                    ->imageWidth(70)
                    ->circular(),*/

                TextColumn::make('source_type')
                    ->label('نوع المصدر')
                    ->badge(),
                TextColumn::make('country')
                    ->label('البلد')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('external_ref')
                    ->label('المرجع')
                    ->searchable(),

                TextColumn::make('foodForm.name_ar')
                    ->label('الحالة'),

                TextColumn::make('status')
                    ->label('حالة السجل')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        FoodSourceStatus::ACTIVE->value     => 'success',
                        FoodSourceStatus::SUPERSEDED->value => 'warning',
                        FoodSourceStatus::REJECTED->value   => 'danger',
                        default                             => 'gray',
                    })
                    ->formatStateUsing(fn($state) => FoodSourceStatus::from($state)->name),

                TextColumn::make('priority')
                    ->label('الأولوية')
                    ->sortable(),

                IconColumn::make('is_preferred')
                    ->label('مفضل')
                    ->boolean(),

                TextColumn::make('nutrient_values_count')
                    ->counts('nutrientValues')
                    ->label('عدد القيم الغذائية')
                    ->badge()
                    ->color('info'),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('source_type')
                    ->label('نوع المصدر')
                    ->options(FoodSourceType::class),

                SelectFilter::make('country')
                    ->label('البلد')
                    ->options(Country::class),
                SelectFilter::make('status')
                    ->label('حالة السجل')
                    ->options(collect(FoodSourceStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->name])),
            ])
            ->defaultSort('priority')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
