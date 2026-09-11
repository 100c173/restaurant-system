<?php

namespace App\Filament\Resources\FoodCategories\Pages;

use App\Filament\Resources\FoodCategories\FoodCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFoodCategories extends ManageRecords
{
    protected static string $resource = FoodCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
