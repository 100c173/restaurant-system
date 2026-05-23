<?php

namespace App\Filament\App\Resources\Modifiers\Pages;

use App\Filament\App\Resources\Modifiers\ModifierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageModifiers extends ManageRecords
{
    protected static string $resource = ModifierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
