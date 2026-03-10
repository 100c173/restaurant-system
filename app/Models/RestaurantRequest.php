<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class RestaurantRequest extends Model
{
    protected $guarded = [];

    public function restaurantLogo(): MorphOne
    {
        return $this->morphOne(TableImage::class, 'imageable')
            ->where('type', 'logo');
    }

    public function restaurantImages(): MorphMany
    {
        return $this->morphMany(TableImage::class, 'imageable')
            ->where('type', 'gallery');
    }
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
