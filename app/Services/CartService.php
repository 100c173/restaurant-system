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
    public function addItem(string $restaurantId, int $itemId, int $quantity = 1): Cart
    {
        // 1. Resolve the tenant
        $restaurant = Restaurant::active()
            ->findOrFail($restaurantId);

        Tenancy::initialize($restaurant->tenant_id);

        // 2. Enforce single-restaurant rule BEFORE switching DB
        $this->assertSingleTenant($restaurant->tenant_id);

        // 3. Switch to tenant DB and validate the item
        $item = MenuItem::where('id', $itemId)
            ->where('is_available', true)
            ->firstOrFail(); // throws if item doesn't exist or unavailable
        ;

        // 4. Write to central DB with denormalized snapshot
        return Cart::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'tenant_id' => $restaurant->tenant_id,
                'item_id' => $itemId,
            ],
            [
                'quantity' => DB::raw("quantity + {$quantity}"),
                'unit_price' => $item->price,   // snapshot at add-time
                'item_name' => $item->name,
                'description' => $item->description,
            ]
        );
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
}