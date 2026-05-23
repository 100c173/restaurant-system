<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Restaurants\Models\Category;
use Modules\Restaurants\Models\Menu;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\Restaurant;
use PhpParser\Node\Expr\FuncCall;
use Stancl\Tenancy\Facades\Tenancy;


class RestaurantMenuService
{
    public function getMenuData(int $restaurantId): array
    {
        $restaurant = Restaurant::active()
            ->findOrFail($restaurantId);

        Tenancy::initialize($restaurant->tenant_id);
        try {
            $categories = Category::with([
                'menuItems' => fn($q) => $q->available()->ordered(),
            ])
                ->active()
                ->ordered()
                ->get();
            return compact('restaurant', 'categories');
        } finally {

            Tenancy::end();
        }

    }

    public function getItem(int $restaurantId, int $itemId): array
    {
        return Cache::remember(
            "restaurant:$restaurantId:item:$itemId",
            300,
            function () use ($restaurantId, $itemId) {

                $restaurant = Restaurant::active()
                    ->findOrFail($restaurantId);

                Tenancy::initialize($restaurant->tenant_id);

                try {
                    $item = MenuItem::with([
                        'variants',
                        'modifierGroups' => function ($query) use ($itemId) {
                            $query->with([
                                'modifiers' => function ($q) use ($itemId) {
                                    $q->wherePivot('menu_item_id', $itemId);
                                }
                            ]);
                        },
                    ])->findOrFail($itemId);
                    return compact('restaurant', 'item');
                } finally {
                    Tenancy::end();
                }
            }
        );
    }
}
