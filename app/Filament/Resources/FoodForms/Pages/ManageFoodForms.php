<?php

namespace App\Filament\Resources\FoodForms\Pages;

use App\Filament\Resources\FoodForms\FoodFormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageFoodForms extends ManageRecords
{
    protected static string $resource = FoodFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
