<?php

namespace App\Filament\Resources\DeliveryRequests;

use App\Filament\Resources\DeliveryRequests\Pages\CreateDeliveryRequest;
use App\Filament\Resources\DeliveryRequests\Pages\EditDeliveryRequest;
use App\Filament\Resources\DeliveryRequests\Pages\ListDeliveryRequests;
use App\Filament\Resources\DeliveryRequests\Pages\ViewDeliveryRequest;
use App\Filament\Resources\DeliveryRequests\Schemas\DeliveryRequestForm;
use App\Filament\Resources\DeliveryRequests\Schemas\DeliveryRequestInfolist;
use App\Filament\Resources\DeliveryRequests\Tables\DeliveryRequestsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Deliveries\Models\DeliveryRequest;
use UnitEnum;

class DeliveryRequestResource extends Resource
{
    protected static ?string $model = DeliveryRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';
    protected static string|UnitEnum|null $navigationGroup = "Deliveries Management" ;

    public static function form(Schema $schema): Schema
    {
        return DeliveryRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DeliveryRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryRequestsTable::configure($table);
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
            'index' => ListDeliveryRequests::route('/'),
            'view' => ViewDeliveryRequest::route('/{record}'),
            'edit' => EditDeliveryRequest::route('/{record}/edit'),
        ];
    }
}
