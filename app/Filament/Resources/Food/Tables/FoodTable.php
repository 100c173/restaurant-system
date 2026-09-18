<?php
namespace App\Filament\Resources\Food\Tables;

use App\Models\TableImage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Schemas\Components\Image;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FoodTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('img')
                ->label('#')
                ->getStateUsing(function($record){
                    return asset('storage/'.$record->img);
                })
                ->imageHeight(70)
                ->imageWidth(70)
                ->circular(),
                TextColumn::make("name_ar")
                    ->label("الاسم"),
                TextColumn::make("category.name")
                    ->label("التصنيف")
                    ->searchable(),
                IconColumn::make("is_active")
                    ->label("فعال")
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('تاريخ الإضافة')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('حالة الطعام')
                    ->options([
                        '1' => 'فعال',
                        '0' => 'غير فعال',
                    ]),
                SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship(
                        name: 'category',
                        titleAttribute: 'name',
                    ),
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
}
