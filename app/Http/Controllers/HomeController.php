<?php

namespace App\Http\Controllers;

use App\Http\Resources\HomeResources\CategoryResource;
use App\Http\Resources\HomeResources\RestaurantResource;
use App\Services\HomeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class HomeController extends Controller
{
    public function __construct(private HomeService $service)
    {
    }

    public function getHomeData(Request $request)
    {
        $latitude  = $request->has('latitude') ? $request->float('latitude') : null;
        $longitude = $request->has('longitude') ? $request->float('longitude') : null;
        $radiusKm  = $request->float('radiusKm',10);
        $perPage   = $request->integer('perPage',8);


        $categories = Cache::remember('categories.active', now()->addHours(6), function () {
            return $this->service->getAllActiveCategories();
        });

        $hasLocation = $latitude !== null && $longitude !== null;

        $nearRestaurants = ($hasLocation) ? $this->service->getNearBy($latitude, $longitude, $radiusKm, $perPage) : collect();
        $randomRestaurants = $this->service->getRandom($perPage);

        $data = [
            'categories' => CategoryResource::collection($categories),
            'restaurants' => [
                'nearby' => RestaurantResource::collection($nearRestaurants),
                'random' => RestaurantResource::collection($randomRestaurants),
            ],
           
            'location_used' => $hasLocation,
        ];

        return $this->success($data, "all data fetched successfully");


    }
}
