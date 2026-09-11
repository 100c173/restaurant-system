<?php

namespace App\Filament\Resources\Nutrients\Pages;

use App\Filament\Resources\Nutrients\NutrientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageNutrients extends ManageRecords
{
    protected static string $resource = NutrientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
