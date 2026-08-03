<?php

namespace App\Filament\Resources\FoodPortions\Pages;

use App\Filament\Resources\FoodPortions\FoodPortionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFoodPortion extends CreateRecord
{
    protected static string $resource = FoodPortionResource::class;
}