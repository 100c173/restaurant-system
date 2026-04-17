<?php

namespace App\Services;

use Modules\Restaurants\Models\Menu;
use Modules\Restaurants\Models\Restaurant;
use Stancl\Tenancy\Facades\Tenancy;

class RestaurantMenuService
{
    /**
     * Fetch a restaurant from the central DB along with its tenant-side
     * menus, categories, and menu items.
     *
     * Returns an array shaped for RestaurantMenuResource:
     *  [
     *    'restaurant' => Restaurant,
     *    'categories' => Collection,   // id + name only
     *    'menu_items' => Collection,   // menus → categories → items
     *  ]
     */
    public function getMenuData(int $restaurantId): array
    {
       
        $restaurant = Restaurant::with('categories:id,name')
            ->active()
            ->findOrFail($restaurantId);

        Tenancy::initialize($restaurant->tenant_id);

        $menus = Menu::with([
            'categories' => function ($q) {
                $q->active()
                    ->ordered()
                    ->with([
                        'menuItems' => fn($q) => $q->available()->ordered(),
                    ]);
            },
        ])
            ->active()
            ->ordered()
            ->get();

        Tenancy::end();

        
        $categories = $restaurant->categories->map(
            fn($c) => $c->only(['id', 'name'])
        );

        return compact('restaurant', 'categories', 'menus');
    }
}
