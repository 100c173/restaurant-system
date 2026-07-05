<?php

namespace Modules\Orders\Services;
use App\Models\Cart;
use App\Models\Tenant;
use App\Models\User;
use App\Services\CloudinaryUploadService;
use DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Orders\Events\ChangeOrderStatus;
use Modules\Orders\Http\Requests\CheckoutRequest;
use Modules\Orders\Listeners\SynsEditStatusWithCentralDbListenr;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderStatusLog;
use Modules\Orders\Models\TenantOrder;
use Modules\Orders\Models\TenantOrderItem;
use Modules\Orders\Models\TenantOrderItemModifier;
use Modules\Orders\Notifications\NewOrderOwnerNotification;
use Modules\Orders\Notifications\OrderPaymentNotification;
use Modules\Restaurants\Models\Restaurant;
use Stancl\Tenancy\Facades\Tenancy;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Http\UploadedFile;


class OrderService
{
    public function confirme(array $data): Order
    {
        $user = User::where('id', auth()->id())->firstOrFail();

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
        $discount = 0; // extend later with coupon logic
        $total = $subtotal - $discount;

        // ── 4. Generate reference number ──────────────────────────
        $reference = $this->generateReference();

        // ── 5. Write to central DB ────────────────────────────────
        $order = DB::transaction(
            function () use ($user, $data, $restaurant, $reference, $subtotal, $discount, $total) {

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
                    'payment_code' => $data['payment_code'] ?? null,
                    'table_number' => $data['table_number'] ?? null,
                    'customer_name' => $user->name,
                    'customer_phone' => $user->phone,
                    'delivery_address' => $data['delivery_address'],
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

        // ── 8. Notify admins and tenant ───────────────────
        $this->notifyNewOrder($order);
        return $order;
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function generateReference(): string
    {
        $date = now()->format('Ymd');
        $unique = strtoupper(Str::random(6));

        return "ORD-{$date}-{$unique}";
    }

    private function notifyNewOrder(Order $order): void
    {
        // ── Notify all admins ─────────────────────────────────
        $admins = User::role('super-admin')->get();

        FilamentNotification::make()
            ->title('طلب جديد')
            ->body("طلب جديد #{$order->reference_number} من مطعم {$order->restaurant_name}")
            ->icon('heroicon-o-shopping-bag')
            ->iconColor('success')
            ->sendToDatabase($admins);

        // ── Notify restaurant owner ───────────────────────────
        $owner = Tenant::where('id', $order->tenant_id)
            ->with('owner')
            ->sole()
                ?->owner;

        if ($owner) {
            $owner->notify(new NewOrderOwnerNotification($order));
        }
    }

    public function checkout(CheckoutRequest $request): void
    {
        Tenancy::initialize($request->input('tenant_id'));

        try {
            DB::transaction(function () use ($request) {

                $order = TenantOrder::where(
                    'reference_number',
                    $request->input('reference_number')
                )->sole();

                $dataToUpdate = [];

                if ($request->hasFile('invoice')) {

                    $file = $request->file('invoice');

                    $cloudinaryService = new CloudinaryUploadService();


                    if ($order->invoice) {
                        $cloudinaryService->delete($order->invoice);
                    }

                    $path = $cloudinaryService->upload($file->getRealPath(), 'invoices');

                    $dataToUpdate['invoice'] = $path;

                }

                if ($request->filled('payment_code')) {
                    $dataToUpdate['payment_code'] = $request->input('payment_code');
                }


                if (!empty($dataToUpdate)) {
                    $order->update($dataToUpdate);
                }

                $this->notifyUpdateOrder(
                    $order->reference_number,
                    $order->total,
                    $request->input('tenant_id')
                );

                event(new ChangeOrderStatus($order, 'customer'));
            });

        } finally {
            Tenancy::end();
        }
    }

    private function notifyUpdateOrder($reference_number, $total, $tenant_id): void
    {
        // ── Notify restaurant owner ───────────────────────────
        $owner = Tenant::where('id', $tenant_id)
            ->with('owner')
            ->sole()
                ?->owner;

        if ($owner) {
            $owner->notify(
                new OrderPaymentNotification(
                    $reference_number,
                    $total,
                    $tenant_id,
                )
            );
        }
    }

    public function getorderCost($reference_number)
    {
        $centralOrder = Order::where('reference_number', $reference_number)->sole();

        try {
            $centralOrder = Order::where('reference_number', $reference_number)->sole();
            $resturant = $centralOrder->tenant->restaurant;
            Tenancy::initialize($centralOrder->tenant_id);
            $tenantOrder = TenantOrder::where('reference_number', $reference_number)->sole();
            return [$tenantOrder, $resturant];

        } finally {
            Tenancy::end();
        }

    }

    public function cancelOrder($reference_number)
    {
        $centralOrder = Order::where('reference_number', $reference_number)->sole();

        try {
            Tenancy::initialize($centralOrder->tenant_id);

            $order = TenantOrder::where('reference_number', $reference_number)->sole();
            $order->update(['status' => 'cancelled']);

            event(new ChangeOrderStatus($order, 'customer'));


        } finally {
            Tenancy::end();
        }
    }

    public function getOrderDetails($reference_number)
    {
        $centralOrder = Order::where('reference_number', $reference_number)->sole();
        $tenant_id = $centralOrder->tenant_id;

        try {
            Tenancy::initialize($tenant_id);

            $order = TenantOrder::with('items.modifiers')
                ->where('reference_number', $reference_number)
                ->sole();

            $data = $order->items->map(function ($item) {
                return [
                    'item_name' => $item->item_name,
                    'variant_name' => $item->variant_name,
                    'unit_price' => (float) $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => (float) $item->line_total,
                    'modifier_groups' => $item->modifiers
                        ->groupBy('modifier_group_name')
                        ->map(function ($modifiers, $groupName) {
                            return [
                                'name' => $groupName,
                                'modifiers' => $modifiers->map(fn($m) => [
                                    'modifier_name' => $m->modifier_name,
                                    'price' => (float) $m->price,
                                ])->values(),
                            ];
                        })
                        ->values(),
                ];
            });
        } finally {
            Tenancy::end();
        }

        return $data;
    }
}
