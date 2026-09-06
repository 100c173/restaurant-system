<?php

namespace App\Filament\App\Resources\MenuItems;




use App\Filament\App\Resources\MenuItems\Pages\ManageIngredients;
use App\Filament\App\Resources\MenuItems\Pages\ManageMenuItems;
use App\Filament\App\Resources\MenuItems\Schemas\MenuItemForm;
use App\Filament\App\Resources\MenuItems\Tables\MenuItemsTable;
use App\Filament\App\Resources\MenuItems\Pages\ManageModifiers;
use App\Filament\App\Resources\MenuItems\Pages\ManageVariants;



use App\Models\Domain;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Services\PlanLimitChecker;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemIngredient;
use Stancl\Tenancy\Facades\Tenancy;
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
            'index' => ManageMenuItems::route('/'),
            'variants' => ManageVariants::route('/{record}/variants'),
            'modifiers' => ManageModifiers::route('/{record}/modifiers'),
           // 'ingredients' => ManageIngredients::route('/{record}/ingredients'),
        ];
    }
    // MenuItemResource.php
    public static function canCreate(): bool
    {
        return PlanLimitChecker::check(
            featureKey: 'MAX_MENU_ITEMS',
            countCallback: fn() => MenuItem::count(),
            limitReachedBodyTemplate: 'خطتك تسمح بإضافة {limit} عناصر فقط.',
        );
    }

    public static function canAnalysis(): bool
    {
        return PlanLimitChecker::check(
            featureKey: 'MAX_ANALYZED_MENU_ITEMS',
            countCallback: fn() => MenuItemIngredient::count(),
            limitReachedBodyTemplate: 'خطتك تسمح بتحليل {limit} عناصر فقط.',
        );
    }
}
