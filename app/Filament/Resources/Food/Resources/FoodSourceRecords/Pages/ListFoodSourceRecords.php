<?php

namespace App\Filament\Resources\Food\Resources\FoodSourceRecords\Pages;

use App\Filament\Resources\Food\Resources\FoodSourceRecords\FoodSourceRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFoodSourceRecords extends ListRecords
{
    protected static string $resource = FoodSourceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('إضافة مصدر جديد')];
    }
}
