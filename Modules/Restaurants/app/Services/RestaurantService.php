<?php

namespace Modules\Restaurants\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Modules\Restaurants\Models\Restaurant;
use Modules\Restaurants\Models\RestaurantRequest;

class RestaurantService
{

    public function getAllRestaurants(): array
    {
        $page = request('page', 1);
        return Cache::remember("paginated_restaurants_{$page}", 3600, fn() =>
            Restaurant::paginate(perPage: 15)->items());
    }



}
