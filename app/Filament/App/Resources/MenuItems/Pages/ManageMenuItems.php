<?php

namespace App\Filament\App\Resources\MenuItems\Pages;

use App\Filament\App\Resources\MenuItems\MenuItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageMenuItems extends ManageRecords
{
    protected static string $resource = MenuItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Item')
                ->icon('heroicon-o-plus')
                ->authorize(fn (): bool => MenuItemResource::canCreate())
                ->mutateFormDataUsing(function (array $data): array {
                    $activeMenuId = $this->tableFilters['menu']['value'] ?? null;

                    if (filled($activeMenuId)) {
                        $data['menu_id'] = $activeMenuId;
                    }

                    return $data;
                }),
        ];
    }
}
