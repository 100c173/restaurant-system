<?php

namespace App\Services;

use Modules\Restaurants\Models\Category;
use Modules\Restaurants\Models\Menu;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\Restaurant;
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
        $restaurant = Restaurant::active()
            ->findOrFail($restaurantId); 

        Tenancy::initialize($restaurant->tenant_id);

        try {
            $item = MenuItem::findOrFail($itemId);
            return compact('restaurant','item');
        } finally {
            Tenancy::end();
        }
    }
}
