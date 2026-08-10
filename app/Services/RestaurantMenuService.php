<?php

namespace App\Services;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Modules\Restaurants\Models\Category;
use Modules\Restaurants\Models\Menu;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemAnalysis;
use Modules\Restaurants\Models\Restaurant;
use PhpParser\Node\Expr\FuncCall;
use Stancl\Tenancy\Facades\Tenancy;


class RestaurantMenuService
{
    public function getMenuData(int $restaurantId, ?float $lat = null, ?float $lng = null, float $radiusKm = 60): array
    {
        $query = Restaurant::active();

        if ($lat && $lng) {
            $query->withDistance($lat, $lng)
                ->withinRadius($radiusKm);
        }

        $restaurant = $query->findOrFail($restaurantId);

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
                        'modifierGroupsWithModifiers.modifierGroup',
                        'modifierGroupsWithModifiers.modifier',
                    ])->findOrFail($itemId);
                    return compact('restaurant', 'item');
                } finally {
                    Tenancy::end();
                }
            }
        );
    }

    public function showAnalysis(int $restaurantId, int $menuItemId)
    {
        $restaurant = Restaurant::findOrFail($restaurantId);

        Tenancy::initialize($restaurant->tenant_id);

        try {
            $analysis = MenuItemAnalysis::query()
                ->where('menu_item_id', $menuItemId)
                ->with('menuItem')
                ->first();


            return($analysis);
        } finally {
            Tenancy::end();
        }
    }
}
