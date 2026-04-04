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

    public function getNearBy(float $lat, float $lng, float $radiusKm = 10, int $perPage = 8): LengthAwarePaginator
    {
        return $this->restaurant
            ->active()
            ->with('categories:id,name')
            ->nearby($lat, $lng, $radiusKm)
            ->paginate($perPage);
    }

    public function getRandom(int $perPage = 8): LengthAwarePaginator
    {
        return $this->restaurant
            ->active()
            ->with('categories:id,name')
            ->inRandomOrder()
            ->paginate($perPage);
    }

    public function getAllActiveCategories()
    {
        return $this->categoty
            //   ->active()
            ->get();
    }

    public function getRestaurantByCategory($category, ?float $lat, ?float $lng, float $radiusKm = 10, int $perPage = 8): LengthAwarePaginator
    {
        $query = $category->restaurants();

        if ($lat && $lng)
            $query->selectRaw("
            (6371 * acos(
                cos(radians(?)) 
                * cos(radians(latitude)) 
                * cos(radians(longitude) - radians(?)) 
                + sin(radians(?)) 
                * sin(radians(latitude))
            )) AS distance
        ", [$lat, $lng, $lat])
                ->whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->having("distance", "<=", $radiusKm)
                ->orderBy("distance");

        return $query->paginate($perPage);
    }


}
