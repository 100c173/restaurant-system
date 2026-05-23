<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
// use Modules\Restaurants\Database\Factories\ModifierFactory;

class Modifier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'price', 'is_available', 'position'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_available' => 'boolean',
        'position' => 'integer',
    ];
    public function modifierGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            ModifierGroup::class,
            'menu_item_modifier_group_modifiers',
            'modifier_id',
            'modifier_group_id'
        )->withPivot(['menu_item_id', 'price_override', 'is_available'])
            ->withTimestamps();
    }
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('is_available', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('name');
    }



}
