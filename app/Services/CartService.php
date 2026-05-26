<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartModifierSelection;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemVariant;
use Modules\Restaurants\Models\Modifier;
use Modules\Restaurants\Models\ModifierGroup;
use Modules\Restaurants\Models\Restaurant;
use Stancl\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartService
{
    public function addItem(array $data): Cart
    {
        $restaurant = Restaurant::findOrFail($data['restaurant_id']);

        Tenancy::initialize($restaurant->tenant_id);

        try {
            $item = MenuItem::findOrFail($data['item_id']);
            $variant = MenuItemVariant::findOrFail($data['variant_id']);

            if (!$variant->is_available) {
                throw ValidationException::withMessages([
                    'variant_id' => 'هذا الخيار غير متاح حالياً.',
                ]);
            }

            $modifierSnapshots = $this->resolveModifierSnapshots(
                $data['modifier_selections'] ?? []
            );

        } finally {
            Tenancy::end();
        }

        $modifiersTotal = collect($modifierSnapshots)->sum('price');
        $unitPrice = $variant->price + $modifiersTotal;

        // Use the central connection explicitly
        $cart = DB::connection('central')->transaction(function () use ($data, $item, $variant, $unitPrice, $modifierSnapshots, $restaurant) {

            // Find existing cart row to get current quantity
            $existing = Cart::where([
                'user_id' => auth()->id(),
                'tenant_id' => $restaurant->tenant_id,
                'item_id' => $data['item_id'],
                'variant_id' => $data['variant_id'],
            ])->first();

            $newQuantity = ($existing ? $existing->quantity : 0) + (int) $data['quantity'];

            $cart = Cart::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'tenant_id' => $restaurant->tenant_id,
                    'item_id' => $data['item_id'],
                    'variant_id' => $data['variant_id'],
                ],
                [
                    'unit_price' => $unitPrice,
                    'item_name' => $item->name,
                    'variant_name' => $variant->name,
                    'description' => $item->description,
                    'quantity' => $newQuantity,  // plain int now ✓
                ]
            );

            $cart->modifierSelections()->delete();

            foreach ($modifierSnapshots as $snapshot) {
                $cart->modifierSelections()->create($snapshot);
            }

            return $cart;
        });

        return $cart->load('modifierSelections');
    }

    // -------------------------------------------------------------------------
    // Called while tenancy is already initialized — no need to init again
    // -------------------------------------------------------------------------
    private function resolveModifierSnapshots(array $selections): array
    {
        if (empty($selections)) {
            return [];
        }

        $snapshots = [];

        foreach ($selections as $selection) {
            $group = ModifierGroup::find($selection['modifier_group_id']);
            $modifier = Modifier::find($selection['modifier_id']);

            if (!$group || !$modifier) {
                throw ValidationException::withMessages([
                    'modifier_selections' => 'Modifier not found.',
                ]);
            }

            $snapshots[] = [
                'modifier_group_id' => $group->id,
                'modifier_group_name' => $group->name,
                'modifier_id' => $modifier->id,
                'modifier_name' => $modifier->name,
                'price' => $modifier->price,
            ];
        }

        return $snapshots;
    }

    public function removeItem(int $cartId): void
    {
        Cart::where('id', $cartId)
            ->where('user_id', auth()->id())
            ->firstOrFail()
            ->delete();
    }

    public function clearCart(): void
    {
        Cart::where('user_id', auth()->id())->delete();
    }
}