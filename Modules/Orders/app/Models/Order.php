<?php

namespace Modules\Orders\Models;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Orders\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;
    protected $connection = 'central';

    protected $fillable = [
        'reference_number',
        'user_id',
        'tenant_id',
        'restaurant_name',
        'type',
        'status',
        'payment_method',
        'payment_status',
        'payment_reference',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'total',
        'delivery_address',
        'delivery_lat',
        'delivery_lng',
        'driver_id',
        'special_instructions',
        'placed_at',
        'confirmed_at',
        'ready_at',
        'dispatched_at',
        'delivered_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'delivery_fee'    => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total'           => 'decimal:2',
        'delivery_lat'    => 'decimal:7',
        'delivery_lng'    => 'decimal:7',
        'placed_at'       => 'datetime',
        'confirmed_at'    => 'datetime',
        'ready_at'        => 'datetime',
        'dispatched_at'   => 'datetime',
        'delivered_at'    => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isDelivery(): bool
    {
        return $this->type === 'delivery';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }
}
