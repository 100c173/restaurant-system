<?php

namespace App\Filament\Owner\Resources\MenuItems;

use App\Filament\Owner\Resources\MenuItems\Pages\CreateMenuItem;
use App\Filament\Owner\Resources\MenuItems\Pages\EditMenuItem;
use App\Filament\Owner\Resources\MenuItems\Pages\ListMenuItems;
use App\Filament\Owner\Resources\MenuItems\Pages\ViewMenuItem;
use App\Filament\Owner\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\Owner\Resources\MenuItems\Schemas\MenuItemInfolist;
use App\Filament\Owner\Resources\MenuItems\Tables\MenuItemsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Restaurants\Models\MenuItem;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MenuItemForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return MenuItemInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenuItemsTable::configure($table);
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
            'index' => ListMenuItems::route('/'),
            'create' => CreateMenuItem::route('/create'),
            'view' => ViewMenuItem::route('/{record}'),
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
