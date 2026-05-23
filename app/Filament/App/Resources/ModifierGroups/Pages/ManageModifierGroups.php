<?php

namespace App\Filament\App\Resources\ModifierGroups\Pages;

use App\Filament\App\Resources\ModifierGroups\ModifierGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageModifierGroups extends ManageRecords
{
    protected static string $resource = ModifierGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
