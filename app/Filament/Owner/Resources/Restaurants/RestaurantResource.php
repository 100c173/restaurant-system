<?php

namespace App\Filament\Owner\Resources\Restaurants;

use App\Filament\Owner\Resources\Restaurants\Pages\CreateRestaurant;
use App\Filament\Owner\Resources\Restaurants\Pages\EditRestaurant;
use App\Filament\Owner\Resources\Restaurants\Pages\ListRestaurants;
use App\Filament\Owner\Resources\Restaurants\Pages\ViewRestaurant;
use App\Filament\Owner\Resources\Restaurants\Schemas\RestaurantForm;
use App\Filament\Owner\Resources\Restaurants\Schemas\RestaurantInfolist;
use App\Filament\Owner\Resources\Restaurants\Tables\RestaurantsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Modules\Restaurants\Models\Restaurant;

class RestaurantResource extends Resource
{
    protected static ?string $model = Restaurant::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return RestaurantForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return RestaurantInfolist::configure($schema);
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
            'create' => CreateRestaurant::route('/create'),
            'view' => ViewRestaurant::route('/{record}'),
            'edit' => EditRestaurant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();

        return parent::getEloquentQuery()
            ->where('owner_id', $user->id)
            ->where('status', 'approved');
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user->hasRole('restaurant-owner')
            && $user->restaurant
            && $user->restaurant->status === 'approved';
    }
    public static function canCreate(): bool
    {
        return false;
    }
}
