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
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Modules\Restaurants\Models\MenuItem;
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
            'ingredients' => ManageIngredients::route('/{record}/ingredients'),
        ];
    }
    public static function canCreate(): bool
    {
        $user = auth()->user();

        $domain = Domain::where('domain', request()->getHost())->first();


        if (!$domain) {
            return false;
        }

        $tenant = Tenant::find($domain->tenant_id);

        // Make sure this tenant actually belongs to the logged-in user
        if (!$tenant || $tenant->owner_id !== $user->id) {
            return false;
        }   


        $subscription = Subscription::where('tenant_id', $tenant->id)
            ->firstOrFail();


        $plan = Plan::findOrFail($subscription->plan_id);

        Tenancy::initialize($tenant->id);

        try {
            $limit = $plan->featureValue('MAX_MENU_ITEMS');

            // Unlimited
            if ($limit === null) {
                return true;
            }

            $count = MenuItem::count();
            if ($count >= $limit) {
                Notification::make()
                    ->title('تم الوصول للحد الأقصى')
                    ->body("خطتك تسمح بإضافة {$limit} عناصر فقط.")
                    ->danger()
                    ->send();

                return false;
            }

            return true;

        } finally {
            Tenancy::end();
        }
    }
}