<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function variants(): HasMany
    {
        return $this->hasMany(MenuItemVariant::class);
    }

    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ModifierGroup::class,
            'menu_item_modifier_group_modifiers',
            'menu_item_id',
            'modifier_group_id',
        )->withPivot(['modifier_id', 'price_override', 'is_available'])
            ->withTimestamps();
    }
    // MenuItem.php
    public function modifierGroupsWithModifiers(): HasMany
    {
        return $this->hasMany(MenuItemModifier::class)
            ->with('modifierGroup', 'modifier');
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
     * Returns true if this item has at least one variant defined.
     * When variants exist, the base price becomes a "from $X" display price.
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Returns the lowest variant price, or the base price if no variants.
     */
    public function startingPrice(): string
    {
        if ($this->hasVariants()) {
            $min = $this->variants()->available()->min('price');
            return number_format($min ?? $this->price, 2);
        }

        return number_format($this->price, 2);
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
