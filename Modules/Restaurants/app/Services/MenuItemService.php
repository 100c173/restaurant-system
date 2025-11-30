<?php

namespace Modules\Restaurants\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Modules\Restaurants\Models\Restaurant;

class MenuItemService
{
    public function getMenuItems(string $id):LengthAwarePaginator
    {
        $restaurant = Restaurant::findOrFail($id);
        return Cache::remember("restauant-menuItems-{$id}", 3600, function () use ($restaurant) {
            return $restaurant->menuItems()->paginate(10);
        });
    }
}
