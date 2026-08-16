<?php

namespace Modules\Orders\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Orders\Models\TenantOrder;

class OrderStatusChanged implements ShouldBroadcast , ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels ;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public TenantOrder $order
    ) {
    }

    /**
     * Get the channels the event should be broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('orders.' . $this->order->central_order_id),
        ];
    }
    public function broadcastAs(): string
    {
        return 'order.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'order_id' => $this->order->central_order_id,
            'reference_number' => $this->order->reference_number,
            'status' => $this->order->status,
            'delivery_cost' =>$this->order->delivery_cost,
            'confirmed_at' => $this->order->confirmed_at?->toIso8601String(),
            'ready_at' => $this->order->ready_at?->toIso8601String(),
            'updated_at' => $this->order->updated_at->toIso8601String(),
        ];
    }
}
