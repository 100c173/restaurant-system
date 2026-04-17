<?php

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
                ->mutateFormDataUsing(function (array $data): array {
                    // Read the active menu filter from the table's live state
                    $activeMenuId = $this->tableFilters['menu']['value'] ?? null;

                    if (filled($activeMenuId)) {
                        $data['menu_id'] = $activeMenuId;
                    }

                    return $data;
                }),
        ];
    }
}