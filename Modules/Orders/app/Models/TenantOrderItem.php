<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Orders\Database\Factories\TenantOrderItemFactory;

class TenantOrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_id',
        'variant_id',
        'item_name',
        'variant_name',
        'unit_price',
        'quantity',
        'line_total',
        'special_note',
        'payment_code'
    ];

    protected $table = 'order_items';

    protected $casts = [
        'unit_price' => 'decimal:2',
        'line_total' => 'decimal:2',
        'quantity' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(TenantOrder::class, 'order_id');
    }

    public function modifiers(): HasMany
    {
        return $this->hasMany(TenantOrderItemModifier::class, 'order_item_id');
    }
}
