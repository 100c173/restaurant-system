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

    public function index()
    {
        $cart = $this->cartService->getGroupedCart();
        return self::success($cart) ;
    }

    public function add(AddToCartRequest $request)
    {
        $result = $this->cartService->addItem(
            data: $request->validated()
        );
        return self::success( $result,'Item added to cart.');

    }

    public function clear()
    {
        $this->cartService->clearCart();
        return $this->success(null, 'your cart cleared successfully.');
    }

    public function destroy($itemId)
    {
        $this->cartService->removeItem($itemId);
        return $this->success(null, 'item removed successfully.');
    }
}
