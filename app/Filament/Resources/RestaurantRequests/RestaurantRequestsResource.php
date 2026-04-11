<?php

namespace App\Filament\Resources\RestaurantRequests;

use App\Filament\Resources\RestaurantRequests\Pages\CreateRestaurantRequests;
use App\Filament\Resources\RestaurantRequests\Pages\EditRestaurantRequests;
use App\Filament\Resources\RestaurantRequests\Pages\ListRestaurantRequests;
use App\Filament\Resources\RestaurantRequests\Schemas\RestaurantRequestsForm;
use App\Filament\Resources\RestaurantRequests\Tables\RestaurantRequestsTable;

use App\Models\RestaurantRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RestaurantRequestsResource extends Resource
{
    protected static ?string $model = RestaurantRequest::class;

    protected static string|UnitEnum|null $navigationGroup = "Requests";

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    public static function form(Schema $schema): Schema
    {
        return RestaurantRequestsForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RestaurantRequestsTable::configure($table);
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
            'index' => ListRestaurantRequests::route('/'),
            'edit' => EditRestaurantRequests::route('/{record}/edit'),
            'create' => CreateRestaurantRequests::route('/create'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
