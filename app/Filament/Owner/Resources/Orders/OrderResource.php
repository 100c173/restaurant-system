<?php

namespace App\Filament\Owner\Resources\Orders;

use App\Filament\Owner\Resources\Orders\Pages\CreateOrder;
use App\Filament\Owner\Resources\Orders\Pages\EditOrder;
use App\Filament\Owner\Resources\Orders\Pages\ListOrders;
use App\Filament\Owner\Resources\Orders\Pages\ViewOrder;
use App\Filament\Owner\Resources\Orders\Schemas\OrderForm;
use App\Filament\Owner\Resources\Orders\Schemas\OrderInfolist;
use App\Filament\Owner\Resources\Orders\Tables\OrdersTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Database\Eloquent\Builder;
use Modules\Orders\Models\Order;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return OrderForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return OrderInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return OrdersTable::configure($table);
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
            'index' => ListOrders::route('/'),
           // 'create' => CreateOrder::route('/create'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        return parent::getEloquentQuery()
            ->where('restaurant_id', $user->restaurant->id) ;
    }
}
