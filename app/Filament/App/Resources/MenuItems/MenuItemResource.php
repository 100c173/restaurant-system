<?php

namespace App\Filament\App\Resources\MenuItems;

use App\Filament\App\Resources\MenuItems\Pages\CreateMenuItem;
use App\Filament\App\Resources\MenuItems\Pages\EditMenuItem;
use App\Filament\App\Resources\MenuItems\Pages\ListMenuItems;
use App\Filament\App\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\App\Resources\MenuItems\Tables\MenuItemsTable;
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
            'edit' => EditMenuItem::route('/{record}/edit'),
        ];
    }
}
