<?php

namespace App\Filament\App\Resources\MenuItemAnalyses\Pages;

use App\Filament\App\Resources\MenuItemAnalyses\MenuItemAnalysisResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMenuItemAnalyses extends ListRecords
{
    protected static string $resource = MenuItemAnalysisResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}