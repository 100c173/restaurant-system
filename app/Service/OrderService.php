<?php

namespace App\Service;
use Illuminate\Database\Eloquent\Collection;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\TenantOrder;
use Stancl\Tenancy\Facades\Tenancy;

class OrderService
{
    public function listForUser(int $userId, array $filters = [], int $perPage = 15):Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('status' , '!=' , 'cancelled')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->latest('placed_at')
            ->get();
    }

    public function getDeliverCost($reference_number){
        $centralOrder = Order::where('reference_number',$reference_number)->sole();

        try{
            Tenancy::initialize($centralOrder->tenant_id);
            $order = TenantOrder::where('reference_number',$reference_number)->sole();
            return $order->delivery_cost ;

        }finally{
            Tenancy::end();
        }

    }
}
