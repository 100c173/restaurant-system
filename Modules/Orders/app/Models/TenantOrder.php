<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Orders\Database\Factories\TenantOrderFactory;

class TenantOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'central_order_id',
        'reference_number',
        'status',
        'type',
        'table_number',
        'customer_name',
        'customer_phone',
        'delivery_address',
        'special_instructions',
        'subtotal',
        'total',
        'confirmed_at',
        'ready_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'ready_at' => 'datetime',
    ];

    protected $table = 'orders';

    // ─── Relationships ────────────────────────────────────────────

    public function items(): HasMany
    {
        return $this->hasMany(TenantOrderItem::class, 'order_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }
}
