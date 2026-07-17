<?php

namespace App\Filament\Resources\FoodNutrients\Pages;

use App\Filament\Resources\FoodNutrients\FoodNutrientResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFoodNutrient extends EditRecord
{
    protected static string $resource = FoodNutrientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
