<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'interval',
        'trial_days',
        'features',
        'is_active'
    ];

    protected $casts = [
        'features' => 'array',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subsecription::class);
    }
}
