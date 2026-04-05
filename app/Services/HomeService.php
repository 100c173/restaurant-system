<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\Restaurants\Models\Restaurant;


class HomeService
{
    public function __construct(
        private readonly Restaurant $restaurant,
        private readonly Category $categoty
    ) {
    }

    public function getNearBy(float $lat, float $lng, float $radiusKm = 10)
    {
        return $this->restaurant
            ->active()
            ->with('categories:id,name')
            ->withDistance($lat, $lng)
            ->withinRadius($radiusKm)
            ->limit(5)
            ->get();
    }

    public function getRandom(?float $lat = null, ?float $lng = null)
    {
        $query = $this->restaurant
            ->active()
            ->with('categories:id,name');

        if ($lat && $lng) {
            $query->withDistance($lat, $lng);
        }

        return $query
            ->inRandomOrder()
            ->limit(6)
            ->get();
    }

    public function getAllActiveCategories()
    {
        return $this->categoty
            //   ->active()
            ->get();
    }

    public function getRestaurantByCategory( $category, ?float $lat, ?float $lng, float $radiusKm = 10, int $perPage = 8): LengthAwarePaginator {

        $query = $category->restaurants()
            ->with('categories:id,name');

        if ($lat && $lng) {
            $query->withDistance($lat, $lng)
                ->withinRadius($radiusKm);
        }

        return $query->paginate($perPage);
    }


}
