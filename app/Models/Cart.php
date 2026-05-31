<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cart extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'item_id',
        'item_name',
        'variant_name',
        'variant_id',
        'description',
        'unit_price',
        'quantity',
        'fingerprint',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
    ];
    protected $connection = 'central';

    // ─── Relationships ────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function modifierSelections()
    {
        return $this->hasMany(CartModifierSelection::class);
    }

    // ─── Computed ─────────────────────────────────────────────────

    /**
     * Line total: unit_price × quantity
     */
    public function getLineTotalAttribute(): string
    {
        return number_format(
            (float) $this->unit_price * $this->quantity,
            2,
            '.',
            ''
        );
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForTenant(Builder $query, string $tenantId): Builder
    {
        return $query->where('tenant_id', $tenantId);
    }
}
