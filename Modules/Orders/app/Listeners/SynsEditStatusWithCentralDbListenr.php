<?php

namespace Modules\Orders\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Orders\Events\ChangeOrderStatus;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderStatusLog;

class SynsEditStatusWithCentralDbListenr
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
    public function handle(ChangeOrderStatus $event): void
    {
        $tenantOrder = $event->order;
        $centralOrder = Order::where('id', $tenantOrder->central_order_id)->sole();
        $centralOrder->update([
            'status' => $tenantOrder->status,
            'payment_status' => ($tenantOrder->invoice) ? 'paid' : 'pending' ,
        ]);
        OrderStatusLog::create([
            'order_id' => $centralOrder->id,
            'status' => $tenantOrder->status,
            'changed_by_type' => $event->actor,
            'changed_by_id' => auth()->id(),
            'created_at' => now(),
        ]);
    }
}
