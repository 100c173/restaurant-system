<?php

namespace App\Services;

use App\Exceptions\CartTenantMismatchException;
use App\Models\Cart;
use App\Models\Tenant;
use DB;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\Restaurant;
use Stancl\Tenancy\Facades\Tenancy;

class CartService
{
    /**
     * Add an item to the cart, enforcing:
     * - Item exists in the tenant's DB
     * - Item is available
     * - Cart belongs to only one tenant (no cross-restaurant mixing)
     */
    public function addItem($data): Cart
    {
        $restaurantId = $data['restaurant_id'];
        $itemId = $data['item_id'];
        $variantId = $data['variant_id'];
        $quantity = $data['quantity'];
        $description = $data['description'];

        // 1. Resolve the tenant
        $restaurant = Restaurant::findOrFail($restaurantId);

        // 2. Enforce single-restaurant rule BEFORE switching DB
        $this->assertSingleTenant($restaurant->tenant_id);

        Tenancy::initialize($restaurant->tenant_id);

        // 3. Switch to tenant DB and validate the item
        $item = MenuItem::where('id', $itemId)
            ->where('is_available', true)
            ->firstOrFail(); // throws if item doesn't exist or unavailable
        ;

        $itemVariant = $item->variants()->findOrFail($variantId);

        // 4. Write to central DB with denormalized snapshot

        $cart = Cart::where([
            'user_id' => auth()->id(),
            'tenant_id' => $restaurant->tenant_id,
            'item_id' => $itemId,
            'variant_id' => $variantId,
        ])->first();
        if ($cart) {
            // Row exists → increment
            $cart->increment('quantity', $quantity);
            $cart->update([
                'unit_price' => $itemVariant->price,
                'item_name' => $item->name,
                'description' => $description,
            ]);
        } else {
            // New row → insert with concrete int
            $cart = Cart::create([
                'user_id'      => auth()->id(),
                'tenant_id'    => $restaurant->tenant_id,
                'item_id'      => $itemId,
                'variant_id'   =>$variantId,
                'item_name'    => $item->name,
                'variant_name' =>$itemVariant->name,
                'quantity'     => $quantity,        // plain int, safe for INSERT
                'unit_price'   => $itemVariant->price,
                'description'  => $description,
            ]);
        }
        return $cart;
    }

    /**
     * Enforce the single-restaurant constraint.
     * If the cart already has items from a different tenant, reject or clear.
     */
    private function assertSingleTenant(string $incomingTenantId): void
    {
        $existingTenantId = Cart::where('user_id', auth()->id())
            ->value('tenant_id');

        if ($existingTenantId && $existingTenantId !== $incomingTenantId) {
            throw new CartTenantMismatchException(
                existing: $existingTenantId,
                incoming: $incomingTenantId
            );
        }
    }

    public function deleteAllitem()
    {
        Cart::query()->delete();
    }

    public function removeItem($itemId)
    {
        $item = Cart::where('item_id', $itemId)->firstOrFail();
        $item->delete();
    }
}