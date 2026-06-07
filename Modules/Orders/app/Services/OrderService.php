<?php

namespace Modules\Orders\Services;
use App\Models\Cart;
use App\Models\User;
use DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderStatusLog;
use Modules\Orders\Models\TenantOrder;
use Modules\Orders\Models\TenantOrderItem;
use Modules\Orders\Models\TenantOrderItemModifier;
use Modules\Restaurants\Models\Restaurant;
use Stancl\Tenancy\Facades\Tenancy;

class OrderService
{
    public function checkout(array $data): Order
    {
        $user = User::where('id',auth()->id())->firstOrFail() ;

        // ── 1. Load cart items for this tenant ────────────────────
        $cartItems = Cart::with('modifierSelections')
            ->where('user_id', $user->id)
            ->where('tenant_id', $data['tenant_id'])
            ->get();

        if ($cartItems->isEmpty()) {
            throw ValidationException::withMessages([
                'cart' => 'السلة فارغة.',
            ]);
        }

        // ── 2. Load restaurant ────────────────────────────────────
        $restaurant = Restaurant::where('tenant_id', $data['tenant_id'])
            ->firstOrFail();

        // ── 3. Calculate financials ───────────────────────────────
        $subtotal = $cartItems->sum(fn($item) => $item->unit_price * $item->quantity);
        $deliveryFee = $this->resolveDeliveryFee($data);
        $discount = 0; // extend later with coupon logic
        $total = $subtotal + $deliveryFee - $discount;

        // ── 4. Generate reference number ──────────────────────────
        $reference = $this->generateReference();

        // ── 5. Write to central DB ────────────────────────────────
        $order = DB::transaction(
            function () use ($user, $data, $restaurant, $reference, $subtotal, $deliveryFee, $discount, $total) {

                $order = Order::create([
                    'reference_number' => $reference,
                    'user_id' => $user->id,
                    'tenant_id' => $data['tenant_id'],
                    'restaurant_name' => $restaurant->name,
                    'type' => $data['type'],
                    'status' => 'pending',
                    'payment_method' => $data['payment_method'],
                    'payment_status' => 'pending',
                    'subtotal' => $subtotal,
                    'delivery_fee' => $deliveryFee,
                    'discount_amount' => $discount,
                    'total' => $total,
                    'delivery_address' => $data['delivery_address'] ?? null,
                    'delivery_lat' => $data['delivery_lat'] ?? null,
                    'delivery_lng' => $data['delivery_lng'] ?? null,
                    'special_instructions' => $data['special_instructions'] ?? null,
                    'placed_at' => now(),
                ]);

                // Log initial status
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'pending',
                    'changed_by_type' => 'system',
                    'changed_by_id' => null,
                    'note' => 'Order placed.',
                    'created_at' => now(),
                ]);

                return $order;
            }
        );

        // ── 6. Write to tenant DB ─────────────────────────────────
        Tenancy::initialize($data['tenant_id']);

        try {
            DB::transaction(function () use ($order, $user, $data, $cartItems, $subtotal, $total) {

                $tenantOrder = TenantOrder::create([
                    'central_order_id' => $order->id,
                    'reference_number' => $order->reference_number,
                    'status' => 'pending',
                    'type' => $data['type'],
                    'table_number' => $data['table_number'] ?? null,
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                    'delivery_address' =>$data['delivery_address'],
                    'special_instructions' => $data['special_instructions'] ?? null,
                    'subtotal' => $subtotal,
                    'total' => $total,
                ]);

                foreach ($cartItems as $cartItem) {

                    $orderItem = TenantOrderItem::create([
                        'order_id' => $tenantOrder->id,
                        'item_id' => $cartItem->item_id,
                        'variant_id' => $cartItem->variant_id,
                        'item_name' => $cartItem->item_name,
                        'variant_name' => $cartItem->variant_name,
                        'unit_price' => $cartItem->unit_price,
                        'quantity' => $cartItem->quantity,
                        'line_total' => $cartItem->unit_price * $cartItem->quantity,
                        'special_note' => $cartItem->special_note,
                    ]);

                    foreach ($cartItem->modifierSelections as $modifier) {
                        TenantOrderItemModifier::create([
                            'order_item_id' => $orderItem->id,
                            'modifier_group_id' => $modifier->modifier_group_id,
                            'modifier_group_name' => $modifier->modifier_group_name,
                            'modifier_id' => $modifier->modifier_id,
                            'modifier_name' => $modifier->modifier_name,
                            'price' => $modifier->price,
                        ]);
                    }
                }
            });

        } finally {
            Tenancy::end();
        }

        // ── 7. Clear cart items for this tenant ───────────────────
        Cart::where('user_id', $user->id)
            ->where('tenant_id', $data['tenant_id'])
            ->delete();

        return $order;
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function resolveDeliveryFee(array $data): float
    {
        if ($data['type'] !== 'delivery') {
            return 0;
        }

        return 0 ; //logic to generate cost related by distance
    }

    private function generateReference(): string
    {
        $date = now()->format('Ymd');
        $unique = strtoupper(Str::random(6));

        return "ORD-{$date}-{$unique}";
    }
}
