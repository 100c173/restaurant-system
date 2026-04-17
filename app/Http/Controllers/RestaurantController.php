<?php

namespace App\Http\Controllers;

use App\Http\Resources\RestaurantMenuResource;
use App\Services\RestaurantMenuService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(
        private readonly RestaurantMenuService $menuService
    ) {
    }

    /**
     * GET /api/restaurants/{restaurant}/menu
     *
     * Returns basic restaurant info, a flat category list, and all
     * menu items grouped by menu → category.
     */
    public function menu(int $restaurant): JsonResponse
    {
        try {
            $data = $this->menuService->getMenuData($restaurant);
        } catch (ModelNotFoundException) {
            return self::error('Restaurant not found.', 404);
        } catch (\Throwable $e) {
            report($e);
            return self::error('Could not load the menu. Please try again.', 500);
        }

        return self::success(
            new RestaurantMenuResource($data),
            'Menu retrieved successfully.'
        );
    }
}
