<?php

namespace App\Filament\Resources\Restaurants;

use App\Filament\Resources\Restaurants\Pages\CreateRestaurants;
use App\Filament\Resources\Restaurants\Pages\EditRestaurants;
use App\Filament\Resources\Restaurants\Pages\ListRestaurants;
use App\Filament\Resources\Restaurants\Pages\ViewRestaurants;
use App\Filament\Resources\Restaurants\Schemas\RestaurantsForm;
use App\Filament\Resources\Restaurants\Schemas\RestaurantsInfolist;
use App\Filament\Resources\Restaurants\Tables\RestaurantsTable;
use App\Notifications\RestaurantStatusChanged;
use BackedEnum;
use Filament\Resources\Resource;

use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Notification;
use Modules\Restaurants\Models\Restaurant;

class RestaurantsResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RestaurantsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RestaurantsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestaurantsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestaurants::route('/'),
            'create' => CreateRestaurants::route('/create'),
            'view' => ViewRestaurants::route('/{record}'),
            'edit' => EditRestaurants::route('/{record}/edit'),
        ];
    }

}
