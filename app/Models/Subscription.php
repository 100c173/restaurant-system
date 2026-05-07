<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Subscription extends Model
{
    protected $fillable = [
        'tenant_id', 'plan_id',
        'price', 'billing_interval',
        'status',
        'starts_at', 'ends_at', 'trial_ends_at', 'cancelled_at',
        'payment_reference', 'notes',
        'activated_by', 'activated_at',
    ];

    protected $casts = [
        'price'          => 'decimal:2',
        'starts_at'      => 'datetime',
        'ends_at'        => 'datetime',
        'trial_ends_at'  => 'datetime',
        'cancelled_at'   => 'datetime',
        'activated_at'   => 'datetime',
    ];

    protected $connection = 'central' ; 

    // ── Relationships ─────────────────────────────────────────────

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function activatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'activated_by');
    }

    // ── Scopes ────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeForTenant($query, string $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    // ── Helpers ───────────────────────────────────────────────────

    public function isActive(): bool
    {
        return $this->status === 'active'
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial'
            && $this->trial_ends_at?->isFuture();
    }

    public function daysRemaining(): int
    {
        $date = $this->status === 'trial' ? $this->trial_ends_at : $this->ends_at;
        return $date ? max(0, (int) now()->diffInDays($date, false)) : 0;
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'active'    => 'success',
            'trial'     => 'info',
            'past_due'  => 'warning',
            'cancelled' => 'danger',
            'expired'   => 'gray',
            default     => 'gray',
        };
    }
}