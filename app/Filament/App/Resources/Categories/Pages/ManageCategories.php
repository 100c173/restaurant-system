<?php

use App\Filament\App\Resources\Categories\CategoriesResource;
use Filament\Resources\Pages\ManageRecords;

class ManageCategories extends ManageRecords
{
    protected static string $resource = CategoriesResource::class;
}