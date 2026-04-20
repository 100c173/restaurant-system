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
        
        $categories = Category::pluck('id','name');
        Tenancy::end();

        return compact('restaurant', 'categories', 'menus');
    }
}
