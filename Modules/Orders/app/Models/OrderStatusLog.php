<?php

namespace Modules\Orders\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Orders\Database\Factories\OrderStatusLogFactory;

class OrderStatusLog extends Model
{
    use HasFactory;
    protected $connection = 'central';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'status',
        'changed_by_type',
        'changed_by_id',
        'note',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
