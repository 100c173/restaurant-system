<?php

namespace App\Filament\Clusters\FoodDatabase;

use BackedEnum;
use Filament\Clusters\Cluster;
use Filament\Support\Icons\Heroicon;

class FoodDatabaseCluster extends Cluster
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    protected static ?string $navigationLabel = 'Food Database';

    protected static ?int $navigationSort = 1;
}
