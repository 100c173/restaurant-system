<?php
/*
namespace App\Http\Controllers;

use App\Http\Resources\ItemResource;
use App\Http\Resources\RestaurantMenuResource;
use App\Services\CartService;
use App\Services\RestaurantMenuService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpParser\Node\Expr\FuncCall;

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
        } catch (ModelNotFoundException $e) {
            return self::error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            report($e);
            return self::error('Could not load the item. Please try again.', 500);
        }

        return self::success(new ItemResource($data), 'Item retrieved successfully.');
    }
    public function addToCart(AddToCartRequest $request)
    {
        try {
            $cart = $this->cartService->addItem();

            return CartResource::make($cart);

        } catch (CartTenantMismatchException $e) {
            return response()->json([
                'error' => 'cart_tenant_mismatch',
                'message' => 'Your cart has items from another restaurant.',
                'existing_tenant' => $e->existing,
            ], 409);

        } catch (ModelNotFoundException) {
            return response()->json([
                'error' => 'item_unavailable',
                'message' => 'This item is no longer available.',
            ], 422);
        }
    }

}
**/
