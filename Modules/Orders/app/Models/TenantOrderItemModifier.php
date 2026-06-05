<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Orders\Database\Factories\TenantOrderItemModifierFactory;

class TenantOrderItemModifier extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'modifier_group_id',
        'modifier_group_name',
        'modifier_id',
        'modifier_name',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(TenantOrderItem::class, 'order_item_id');
    }
}
