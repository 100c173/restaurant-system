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

    public function getRestaurant(?float $lat = null, ?float $lng = null, float $radiusKm = 60)
    {
        $query = $this->restaurant
            ->active()
            ->with('categories:id,name');
        

        if ($lat && $lng) {
            $query
                ->withDistance($lat, $lng)
                ->withinRadius($radiusKm)
                ->orderBy('distance');;
        }else{
             $query->orderByDesc('rate');
        }

        return $query->get();
    }

    public function getAllActiveCategoriesWithPhoto()
    {
        return $this->categoty
            //   ->active()
            ->get(['id','name','img']);
    }
    public function getAllActiveCategoriesWithoutPhoto()
    {
        return $this->categoty
            //   ->active()
            ->get(['id','name']);
    }

    public function getRestaurantByCategory($category, ?float $lat, ?float $lng, float $radiusKm = 10, int $perPage = 8): LengthAwarePaginator
    {

        $query = $category->restaurants()
            ->with('categories:name');

        if ($lat && $lng) {
            $query->withDistance($lat, $lng)
                ->withinRadius($radiusKm);
        }

        return $query->paginate($perPage);
    }


}
