<?php

namespace Modules\Orders\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Orders\Events\SetDeliveryFee;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderStatusLog;

class SynsDeliveyFeeWithCentralDbListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
    }

    /**
     * Handle the event.
     */
    public function handle(SetDeliveryFee $event): void
    {
        $tenantOrder = $event->order;
        
        $centralOrder = Order::where('id', $tenantOrder->central_order_id)->sole();
        $centralOrder->update([
            'delivery_fee' => $tenantOrder->delivery_cost,
            'subtotal' => $tenantOrder->subtotal,
            'total' => $tenantOrder->total , 

        ]);
        
        OrderStatusLog::create([
            'order_id' => $centralOrder->id,
            'status' => $tenantOrder->status,
            'changed_by_type' => 'restaurant',
            'changed_by_id' => auth()->id(),
            'note' => "set delivey fee " .  $tenantOrder->delivery_cost,
            'created_at' => now(),
        ]);

    }
}
