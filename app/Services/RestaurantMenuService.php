<?php

namespace App\Services;

use Modules\Restaurants\Models\Category;
use Modules\Restaurants\Models\Menu;
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

        $categories = Category::with([
            'menuItems' => fn($q) => $q->available()->ordered(),
        ])
            ->active()
            ->ordered()
            ->get();

        Tenancy::end();

        return compact('restaurant', 'categories');
    }
}
