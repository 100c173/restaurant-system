<?php

namespace App\Filament\App\Resources\MenuItems;




use App\Filament\App\Resources\MenuItems\Pages\ManageIngredients;
use App\Filament\App\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\App\Resources\MenuItems\Tables\MenuItemsTable;
use App\Filament\App\Resources\MenuItems\Pages\ManageModifiers;
use App\Filament\App\Resources\MenuItems\Pages\ManageVariants;



use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ManageMenuItems;
use Modules\Restaurants\Models\MenuItem;
use UnitEnum;

class MenuItemResource extends Resource
{
    protected static ?string $model = MenuItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;
    protected static string|UnitEnum|null $navigationGroup = 'Menu info';
    protected static ?int $navigationSort = 2;

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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'     => ManageMenuItems::route('/'),
            'variants'  => ManageVariants::route('/{record}/variants'),
            'modifiers' => ManageModifiers::route('/{record}/modifiers'),
            'ingredients' => ManageIngredients::route('/{record}/ingredients'),
        ];
    }
}