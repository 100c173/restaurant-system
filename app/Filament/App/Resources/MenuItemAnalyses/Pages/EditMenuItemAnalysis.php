<?php

namespace App\Filament\App\Resources\MenuItemAnalyses\Pages;

use App\Filament\App\Resources\MenuItemAnalyses\MenuItemAnalysisResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMenuItemAnalysis extends EditRecord
{
    protected static string $resource = MenuItemAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}