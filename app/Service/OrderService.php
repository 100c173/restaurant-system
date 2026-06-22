<?php

namespace App\Service;
use Illuminate\Database\Eloquent\Collection;
use Modules\Orders\Models\Order;

class OrderService
{
    public function listForUser(int $userId, array $filters = [], int $perPage = 15):Collection
    {
        return Order::query()
            ->where('user_id', $userId)
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->latest('placed_at')
            ->get();
    }
}
