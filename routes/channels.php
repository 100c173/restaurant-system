<?php

use Illuminate\Support\Facades\Broadcast;
use Modules\Orders\Models\TenantOrder;

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    $order = TenantOrder::find($orderId);

    return $order && ($order->user_id);
});
