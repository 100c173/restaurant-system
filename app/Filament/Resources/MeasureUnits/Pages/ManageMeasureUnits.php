<?php

namespace App\Filament\Resources\MeasureUnits\Pages;

use App\Filament\Resources\MeasureUnits\MeasureUnitResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMeasureUnits extends ManageRecords
{
    protected static string $resource = MeasureUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
