<?php

namespace App\Filament\App\Resources\Menus;

use App\Filament\App\Resources\Menus\Pages\CreateMenu;
use App\Filament\App\Resources\Menus\Pages\EditMenu;
use App\Filament\App\Resources\Menus\Pages\ListMenus;
use App\Filament\App\Resources\Menus\Pages\ManageMenus;
use App\Filament\App\Resources\Menus\Schemas\MenuForm;
use App\Filament\App\Resources\Menus\Tables\MenusTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Restaurants\Models\Menu;
use UnitEnum;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static string|UnitEnum|null $navigationGroup = 'Menu info';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationLabel = 'Menus';

    public static function form(Schema $schema): Schema
    {
        return MenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MenusTable::configure($table);
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
            'index' => ManageMenus::route('/'),
            //'index' => ListMenus::route('/'),
            //'create' => CreateMenu::route('/create'),
            //'edit' => EditMenu::route('/{record}/edit'),
        ];
    }
}
