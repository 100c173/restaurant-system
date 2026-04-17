<?php

namespace App\Filament\App\Resources\Categories;

use App\Filament\App\Resources\Categories\Pages\CreateCategories;
use App\Filament\App\Resources\Categories\Pages\EditCategories;
use App\Filament\App\Resources\Categories\Pages\ListCategories;
use App\Filament\App\Resources\Categories\Schemas\CategoriesForm;
use App\Filament\App\Resources\Categories\Tables\CategoriesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use ManageCategories;
use Modules\Restaurants\Models\Category;
use UnitEnum;

class CategoriesResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;
    protected static string|UnitEnum|null $navigationGroup = 'Menu info';
    protected static ?int $navigationSort = 2;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CategoriesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CategoriesTable::configure($table);
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
            'index' => ManageCategories::route('/'),
        ];
    }
}
