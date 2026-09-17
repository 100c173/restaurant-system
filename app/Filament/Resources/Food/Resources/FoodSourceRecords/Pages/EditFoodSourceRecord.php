<?php
namespace App\Filament\Resources\Food\Resources\FoodSourceRecords\Pages;

use App\Filament\Resources\Food\Resources\FoodSourceRecords\FoodSourceRecordResource;
use App\Filament\Resources\Recipes\RecipeResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFoodSourceRecord extends EditRecord
{
    protected static string $resource = FoodSourceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('manageRecipe')
                ->label(fn() => $this->record->recipe ? 'إدارة الوصفة' : 'إنشاء وصفة من هذا المصدر')
                ->icon('heroicon-o-book-open')
                ->url(fn() => $this->record->recipe
                        ? RecipeResource::getUrl('edit', ['record' => $this->record->recipe])
                        : RecipeResource::getUrl('create', ['food_source_record_id' => $this->record->id])),
            DeleteAction::make(),
        ];
    }
}
