<?php

namespace App\Filament\Resources\FoodPortions\Pages;

use App\Filament\Resources\FoodPortions\FoodPortionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFoodPortion extends EditRecord
{
    protected static string $resource = FoodPortionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}