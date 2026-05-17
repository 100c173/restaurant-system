<?php

namespace App\Http\Controllers;

use App\Exceptions\CartTenantMismatchException;
use App\Http\Requests\AddToCartRequest;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {
    }

    public function store(AddToCartRequest $request)
    {
        try {
           
            $cart = $this->cartService->addItem($request->validated());

            return $this->success(new CartResource($cart) , 'item add to cart successfully') ;

        } catch (CartTenantMismatchException $e) {
            return response()->json([
                'error' => 'cart_tenant_mismatch',
                'message' => 'Your cart has items from another restaurant. you must to refresh your cart.',
                'existing_tenant' => $e->existing,
            ], 409);

        } catch (ModelNotFoundException) {
            return response()->json([
                'error' => 'item_unavailable',
                'message' => 'This item is no longer available.',
            ], 422);
        }
    }

    public function clear()
    {
        $this->cartService->deleteAllitem();
        return $this->success(null,'your cart cleared successfully.');
    }

    public function destroy($itemId){
        $this->cartService->removeItem($itemId);
        return $this->success(null,'item removed successfully.');
    }
}
