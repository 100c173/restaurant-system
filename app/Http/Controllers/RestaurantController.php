<?php

namespace App\Http\Controllers;


use App\Http\Resources\ItemResource;
use App\Http\Resources\RestaurantMenuResource;
use App\Services\CartService;
use App\Services\RestaurantMenuService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    public function __construct(
        private readonly RestaurantMenuService $menuService,
        private readonly CartService $cartService
    ) {
    }


    public function menu(int $restaurant): JsonResponse
    {
        try {
            $data = $this->menuService->getMenuData($restaurant);
            $data['details'] = false ;
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

    public function getItem(int $restaurant, int $menu_item)
    {
        try {
            $data = $this->menuService->getItem($restaurant, $menu_item);
            $data['details'] = true ;
        } catch (ModelNotFoundException $e) {
            return self::error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            report($e);
            return self::error('Could not load the item. Please try again.', 500);
        }
        return self::success(new ItemResource($data), 'Item retrieved successfully.');
    }

}

