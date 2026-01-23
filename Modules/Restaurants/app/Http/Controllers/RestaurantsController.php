<?php

namespace Modules\Restaurants\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Restaurants\Http\Requests\RestaurantRequest;
use Modules\Restaurants\Http\Requests\StoreRestaurantRequest;
use Modules\Restaurants\Services\RestaurantService;
use Illuminate\Validation\ValidationException;

class RestaurantsController extends Controller
{
    public function __construct(private RestaurantService $service)
    {
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $restaurants = $this->service->getAllRestaurants();
            return response()->json($restaurants);
        } catch (\Exception $e) {
            return response()->json(["error" => $e->getMessage()]);
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

    /**
     * Register as a Owner
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws ValidationException
     */
    public function registerAsOwner(StoreRestaurantRequest $request): JsonResponse
    {

        $validated = $request->validated();

        $ownerRequest = $this->service->makeRestaurantRequest($validated);

        return self::success(
            data: [
                'request_id' => $ownerRequest->id ?? null,
                'restaurant_name' => $validated['restaurant_name'],
                'status' => 'pending',
                'address' => $validated['address'],

            ],
            message: 'Your request has been successfully submitted. We will contact you soon.',
            status: 201
        );
    }
}
