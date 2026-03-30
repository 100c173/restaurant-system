<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


// use Modules\Restaurants\Database\Factories\MenuItemFactory;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'image',
        'is_available',
        'position',
        'preparation_time',
        'is_featured',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'is_featured' => 'boolean',
        'position' => 'integer',
        'preparation_time' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function menuItemVariants(): HasMany
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('name');
    }


    /**
     * Returns a human-readable preparation time string.
     * e.g. 75 → "1 hr 15 min"
     */
    public function formattedPreparationTime(): ?string
    {
        if (!$this->preparation_time) {
            return null;
        }

        $hours = intdiv($this->preparation_time, 60);
        $minutes = $this->preparation_time % 60;

        if ($hours > 0 && $minutes > 0) {
            return "{$hours} hr {$minutes} min";
        }

        if ($hours > 0) {
            return "{$hours} hr";
        }

        return "{$minutes} min";
    }


}
