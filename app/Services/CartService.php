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
    public function getGroupedCart()
    {
        $cartItems = Cart::with('modifierSelections')
            ->forUser(auth()->id())
            ->get();

        // Group by tenant_id, then for each group fetch the restaurant name
        return $cartItems
            ->groupBy('tenant_id')
            ->map(function ($items, $tenantId) {
                $restaurant = Restaurant::whereHas('tenant', fn($q) => $q->where('id', $tenantId))
                    ->first();
                return [
                    'is_active' => ($restaurant->isOpenNow() && $restaurant->is_active),
                    'tenant_id' => $tenantId,
                    'restaurant_id' => $restaurant->id,
                    'restaurant_name' => $restaurant->name,
                    'restaurant_logo' => $restaurant->logo,
                    'item_count' => $items->count(),
                    'subtotal' => number_format($items->sum('line_total'), 2, '.', ''),
                    'items' => $items->map(fn($item) => [
                        'item_id'=> $item->id,
                        'item_name' => $item->item_name,
                        'variant_name' => $item->variant_name,
                        'quantity' => $item->quantity,
                        'modifiers_summary' => $item->modifierSelections
                            ->pluck('modifier_name')
                            ->join('، '),
                    ]),
                ];
            })->values(); //  this resets the keys to 0, 1, 2...;
    }

    public function addItem(array $data): Cart
    {
        return $this->handleCartItem($data);
    }

    public function editItem(array $data,$cartId): Cart
    {
        $cart = Cart::where('id', $cartId)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        return $this->handleCartItem($data, $cart);
    }

    private function handleCartItem(array $data, ?Cart $existingCart = null): Cart
    {
        $restaurant = Restaurant::findOrFail($data['restaurant_id']);

        Tenancy::initialize($restaurant->tenant_id);

        try {
            $item = MenuItem::findOrFail($data['item_id']);
            $variant = null;
            $basePrice = $item->price;

            if (!empty($data['variant_id'])) {
                $variant = MenuItemVariant::findOrFail($data['variant_id']);

                if (!$variant->is_available) {
                    throw ValidationException::withMessages([
                        'variant_id' => 'هذا الخيار غير متاح حالياً.',
                    ]);
                }

                $basePrice = $variant->price;
            }

            $modifierSnapshots = $this->resolveModifierSnapshots(
                $data['modifier_selections'] ?? []
            );

        } finally {
            Tenancy::end();
        }

        $modifiersTotal = collect($modifierSnapshots)->sum('price');
        $unitPrice = $basePrice + $modifiersTotal;

        $fingerprint = md5(json_encode([
            'item_id' => $data['item_id'],
            'variant_id' => $data['variant_id'] ?? null,
            'modifiers' => collect($data['modifier_selections'] ?? [])
                ->sortBy(['modifier_group_id', 'modifier_id'])
                ->values()
        ]));

        return DB::transaction(function () use ($data, $existingCart, $restaurant, $item, $variant, $unitPrice, $modifierSnapshots, $fingerprint) {

            if ($existingCart) {

                $existingCart->update([
                    'item_id' => $data['item_id'],
                    'variant_id' => $data['variant_id'] ?? null,
                    'unit_price' => $unitPrice,
                    'item_name' => $item->name,
                    'variant_name' => $variant?->name,
                    'description' => $item->description,
                    'quantity' => (int) $data['quantity'],
                    'fingerprint' => $fingerprint,
                ]);

                $cart = $existingCart;

            } else {

                $existing = Cart::where([
                    'user_id' => auth()->id(),
                    'tenant_id' => $restaurant->tenant_id,
                    'fingerprint' => $fingerprint,
                ])->first();

                $newQuantity = ($existing ? $existing->quantity : 0) + (int) $data['quantity'];

                $cart = Cart::updateOrCreate(
                    [
                        'user_id' => auth()->id(),
                        'tenant_id' => $restaurant->tenant_id,
                        'fingerprint' => $fingerprint,
                    ],
                    [
                        'item_id' => $data['item_id'],
                        'variant_id' => $data['variant_id'] ?? null,
                        'unit_price' => $unitPrice,
                        'item_name' => $item->name,
                        'variant_name' => $variant?->name,
                        'description' => $item->description,
                        'quantity' => $newQuantity,
                    ]
                );
            }

            $this->syncModifiers($cart, $modifierSnapshots);

            return $cart->load('modifierSelections');
        });

    }

    private function syncModifiers(Cart $cart, array $modifierSnapshots): void
    {
        $cart->modifierSelections()->delete();

        foreach ($modifierSnapshots as $snapshot) {
            $cart->modifierSelections()->create($snapshot);
        }
    }

    public function getCartItemsByRestaurant(int $restaurantId): array
    {
        $restaurant = Restaurant::where('id', $restaurantId)->firstOrFail();

        $cartItems = Cart::with('modifierSelections')
            ->where('user_id', auth()->id())
            ->where('tenant_id', $restaurant->tenant_id)
            ->get();

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'السلة فارغة',
            ]);
        }

        $subtotal = $cartItems->sum(fn($item) => $item->unit_price * $item->quantity);

        $payByShamCach = $restaurant->sham_cach_account_barcode ? true : false;

        return [
            'tenant_id' => $restaurant->tenant_id,
            'restaurant_name' => $restaurant->name,
            'restaurant_id' => $restaurant->id,
            'has_delivery' => $restaurant->has_delivery,
            'payByShamCach' => $payByShamCach,
            'cart_items' => $cartItems->map(fn($item) => [
                'item_id' => $item->id, // item in cart
                'item_name' => $item->item_name,
                'variant_name' => $item->variant_name,
                'quantity' => $item->quantity,
                'line_total' => number_format($item->unit_price * $item->quantity, 2, '.', ''),
                'modifiers_summary' => $item->modifierSelections
                    ->pluck('modifier_name')
                    ->join('، '),
            ]),
            'summary' => [
                'items_count' => $cartItems->count(),
                'subtotal' => number_format($subtotal, 2, '.', ''),
            ],
        ];

    }

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

    public function removeCart(int $restaurantId): void
    {
        $tenantId = Restaurant::where('id', $restaurantId)->value('tenant_id');

        Cart::where('tenant_id', $tenantId)
            ->where('user_id', auth()->id())
            ->delete();
    }

    public function clearCart(): void
    {
        Cart::where('user_id', auth()->id())->delete();
    }

    public function deleteItemFromCart($itemId)
    {
        $item = Cart::where('id', $itemId)->firstOrFail();
        $item->delete();
    }

    public function editeQuantity($items)
    {
        DB::transaction(function () use ($items) {
            foreach ($items as $item) {

                $cart = Cart::where('id', $item[0]['item_id']);
                $cart->update([
                    'quantity' => $item[0]['quantity']
                ]);
            }
        });
    }

    public function getItemForEdit($cartId): array
    {
        $cart = Cart::where('id', $cartId)
            ->where('user_id', auth()->id())
            ->with('modifierSelections')
            ->firstOrFail();

        Tenancy::initialize($cart->tenant_id);

        try {
            $item = MenuItem::with(['variants', 'modifierGroupsWithModifiers.modifierGroup', 'modifierGroupsWithModifiers.modifier'])
                ->findOrFail($cart->item_id);

            $selectedModifierIds = $cart->modifierSelections
                ->pluck('modifier_id')
                ->all();

            return [
                'cart_id' => $cart->id,
                'quantity' => $cart->quantity,

                'item' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description,
                    'price' => $item->price,
                ],

                'variants' => $item->variants->map(fn($v) => [
                    'id' => $v->id,
                    'name' => $v->name,
                    'price' => $v->price,
                    'is_available' => $v->is_available,
                    'selected' => $v->id === $cart->variant_id,
                ]),

                'modifier_groups' => $item->modifierGroupsWithModifiers
                    ->groupBy('modifier_group_id')
                    ->map(function ($rows) use ($selectedModifierIds) {
                        $group = $rows->first()->modifierGroup;

                        return [
                            'id' => $group->id,
                            'name' => $group->name,
                            //'min' => $group->min_selections,
                            //'max' => $group->max_selections,
                            'modifiers' => $rows->map(fn($row) => [
                                'id' => $row->modifier->id,
                                'name' => $row->modifier->name,
                                'price' => $row->price_override ?? $row->modifier->price,
                                'is_available' => $row->is_available,
                                'selected' => in_array($row->modifier_id, $selectedModifierIds, true),
                            ])->values(),
                        ];
                    })
                    ->values(),
            ];

        } finally {
            Tenancy::end();
        }
    }
}
