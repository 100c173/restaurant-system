<?php

namespace App\Filament\App\Resources\Menus;

use App\Filament\App\Resources\Menus\Pages\CreateMenu;
use App\Filament\App\Resources\Menus\Pages\EditMenu;
use App\Filament\App\Resources\Menus\Pages\ListMenus;
use App\Filament\App\Resources\Menus\Pages\ViewMenu;
use App\Filament\App\Resources\Menus\RelationManagers\CategoriesRelationManager;
use App\Filament\App\Resources\Menus\Schemas\MenuForm;
use App\Filament\App\Resources\Menus\Tables\MenusTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Restaurants\Models\Menu;

class MenuResource extends Resource
{
    protected static ?string $model = Menu::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

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
            CategoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMenus::route('/'),
            'create' => CreateMenu::route('/create'),
            'view' => ViewMenu::route('/{record}'),
        ];
    }
}
