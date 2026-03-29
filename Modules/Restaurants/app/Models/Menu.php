<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Restaurants\Database\Factories\MenuFactory;

class Menu extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'is_active', 'position', 'available_from', 'available_until'];

    protected $casts = [
        'is_active' => 'boolean',
        'position' => 'integer',
        'available_from' => 'string',   // stored as TIME, returned as "HH:MM:SS"
        'available_until' => 'string',
    ];


    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    // -----------------------------------------------------------------------
    // Scopes
    // -----------------------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('position')->orderBy('name');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Returns true if this menu is currently within its time window,
     * or has no time restriction at all.
     */
    public function isCurrentlyAvailable(): bool
    {
        if (!$this->available_from || !$this->available_until) {
            return true;
        }

        $now = now()->format('H:i:s');
        $from = $this->available_from;
        $until = $this->available_until;

        // Handle overnight windows (e.g. 22:00 → 02:00)
        if ($from <= $until) {
            return $now >= $from && $now <= $until;
        }

        return $now >= $from || $now <= $until;
    }

}
