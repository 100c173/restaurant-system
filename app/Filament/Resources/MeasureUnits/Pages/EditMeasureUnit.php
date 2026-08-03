<?php

namespace App\Filament\Resources\MeasureUnits\Pages;

use App\Filament\Resources\MeasureUnits\MeasureUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMeasureUnit extends EditRecord
{
    protected static string $resource = MeasureUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}