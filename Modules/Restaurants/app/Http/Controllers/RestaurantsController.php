<?php

namespace Modules\Restaurants\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Restaurants\Http\Requests\RestaurantRequest;
use Modules\Restaurants\Services\RestaurantService;

class RestaurantsController extends Controller
{
    public function __construct(private RestaurantService $service) {}
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $restaurants = $this->service->getAllRestaurants();
            return response()->json($restaurants);
        }catch(\Exception $e){
            return response()->json(["error"=> $e->getMessage()]);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //

        return response()->json([]);
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        //

        return response()->json([]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        //

        return response()->json([]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        //

        return response()->json([]);
    }

    public function restaurantRequest(RestaurantRequest $request)
    {
        try {
            $restaurant = $this->service->makeRestaurantRequest($request->validated());
            return response()->json($restaurant);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()]);
        }
    }
}
