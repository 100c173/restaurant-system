<?php

namespace Modules\Orders\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Models\CartItem;
use Modules\Restaurants\Models\MenuItem;

class CartItemService
{
    public function getCartItems(){
        $cartItems = CartItem::forCurrentUser()->get() ;
        return $cartItems ;
    }

    public function addItemToCart(Request $request)
    {
        $user = auth()->user();
        $menuItem = MenuItem::findOrFail($request->menu_item_id);

        $existingCart = CartItem::forCurrentUser()->first();
      
        if ($existingCart &&  $existingCart->menuItem->restaurant_id != $menuItem->restaurant_id) {
            return [
                'success' => false,
                'message' => 'Your cart contains items from another restaurant. Please clear it first.'
            ];
        }

        $item = CartItem::firstOrNew(
            [
                "user_id" => $user->id,
                "menu_item_id" => $request->menu_item_id,
            ]
        );

        $item->quantity = ($item->exists ? $item->quantity : 0) + ($request->quantity ?? 1);
        $item->notes = $request->notes;
        $item->save();

        return [

            'success' => true,
            'message' => 'Item added to cart successfully.',
            'item'    => $item,
        ];
    }

    public function clearCartItems()
    {
        $user = auth()->user();
        $deletedCount = CartItem::forCurrentUser()->delete();

        if ($deletedCount > 0) {
            return [
                'success' => true,
                'message' => " Shopping cart cleared successfully! {$deletedCount} items were removed.",
                'data' => ['deleted_count' => $deletedCount],
                'status_code' => 200,
            ];
        }

        // Case: Cart was already empty
        return [
            'success' => true,
            'message' => 'The shopping cart is already empty.',
            'data' => ['deleted_count' => 0],
            'status_code' => 200,
        ];
    }
}
