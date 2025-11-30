<?php

namespace Modules\Orders\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use Modules\Orders\Events\OrderPlaced;
use Modules\Orders\Models\CartItem;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;

class OrderService
{
    public function checkout()
    {
        $user = auth()->user();

        $cartItems = CartItem::with('menuItem')->forCurrentUser()->get();


        if ($cartItems->isEmpty()) {
            throw new Exception('Cart is empty.');
        }

        DB::transaction(function () use ($user, $cartItems) {

            $subtotal = $cartItems->sum(fn($item) => $item->menuItem->price * $item->quantity);
            $tax = $subtotal * 0.1; // 10%
            $deliveryFee = 5;
            $totalAmount = $subtotal + $tax + $deliveryFee;

            //create order
            $order = Order::create([
                'customer_id' => $user->id,
                'restaurant_id' => $cartItems->first()->menuItem->restaurant_id,
                'order_number' => 'ORD-' . strtoupper(uniqid()),
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cash',
                'delivery_address' => $user->address ?? 'Unknown',
                'delivery_fee' => $deliveryFee,
                'tax_amount' => $tax,
            ]);

            //create order items
            foreach ($cartItems as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'menu_item_id' => $cartItem->menu_item_id,
                    'quantity' => $cartItem->quantity,
                    'unit_price' => $cartItem->menuItem->price,
                    'total_price' => $cartItem->menuItem->price * $cartItem->quantity,
                    'special_instructions' => $cartItem->notes,
                ]);
            }

            //clear the cartItems
            CartItem::forCurrentUser()->delete() ;

            //dispatch after checked out the order
            OrderPlaced::dispatch($order); //event(new OrderPlaced($order));
        });
    }
}
