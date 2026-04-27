<?php

namespace App\Filament\Resources\ShamCashAccounts\Pages;

use App\Filament\Resources\ShamCashAccounts\ShamCashAccountsResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageShamCashAccounts extends ManageRecords
{
    protected static string $resource = ShamCashAccountsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
