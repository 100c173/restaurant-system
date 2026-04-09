<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subsecription extends Model
{
    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'canceled_at'
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && now()->lt($this->ends_at);
    }

    public function onTrial(): bool
    {
        return $this->status === 'trial' && now()->lt($this->trial_ends_at);
    }
}
