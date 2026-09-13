<?php
namespace App\Filament\Resources\Recipes\Tables;

use App\Enums\RecipeStatus;
use App\Filament\Resources\Food\FoodResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RecipesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('food.name_ar')
                    ->label('الطبق')
                    ->searchable()
                    ->url(fn($record) => FoodResource::getUrl('edit', ['record' => $record->food_id])),

                TextColumn::make('servings')->label('الحصص'),

                TextColumn::make('ingredients_count')
                    ->counts('ingredients')
                    ->label('عدد المكونات')
                    ->badge(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn(RecipeStatus $state) => $state->name),

                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(RecipeStatus::cases())->mapWithKeys(fn($c) => [$c->value => $c->name])),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
