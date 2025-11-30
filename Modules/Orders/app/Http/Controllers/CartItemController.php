<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Orders\Http\Requests\CartItemRequest;
use Modules\Orders\Services\CartItemService;

class CartItemController extends Controller
{
    public function __construct(private CartItemService $service) {}

    public function index(){
        try{
            $items = $this->service->getCartItems();
            return response()->json($items);
        }catch(\Exception $e){
              return response()->json(['message'=> $e->getMessage()]);
        }
    }

    public function addToCart(CartItemRequest $request){
        try {
            $result = $this->service->addItemToCart($request);
            if (!$result['success']) {
                return response()->json(['message' => $result['message']], 409);
            }

            return response()->json([
                'message' => $result['message'],
                'data'    => $result['item'],
            ]);

        } catch (\Exception $e) {
            return response()->json(['message'=> $e->getMessage()]);
        }
    }

    public function clearCartItems(){
        try {
            $result = $this->service->clearCartItems();
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json(['message'=> $e->getMessage()]);
        }
    }
}
