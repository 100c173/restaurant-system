<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantRequest extends Model
{
    protected $guarded = [];

    protected $casts = [
        'categories' => 'array',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
