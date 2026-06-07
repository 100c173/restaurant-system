<?php

namespace Modules\Restaurants\Models;

use App\Models\Category;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


// use Modules\Restaurants\Database\Factories\RestaurantFactory;

class Restaurant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "tenant_id",
        "name",
        "description",
        "logo",
        "cover_image",
        "address",
        "phone",
        "email",
        "status",
        "sham_cach_account",
        "is_active",
        "latitude",
        "longitude",
        "opening_time",
        "closing_time",
    ];
    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'is_active' => 'boolean',
        'opening_time' => 'string',
        'closing_time' => 'string',
    ];

    protected $connection = 'central';
    
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class,'tenant_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->BelongsToMany(Category::class, 'restaurant_categories');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: order by distance from a given coordinate.
     * Uses the Haversine formula .
     */

    public function scopeWithDistance($query, float $lat, float $lng)
    {
        $haversine = "(6371 * acos(
        cos(radians(?)) * cos(radians(latitude)) *
        cos(radians(longitude) - radians(?)) +
        sin(radians(?)) * sin(radians(latitude))
    ))";

        return $query
            ->selectRaw("restaurants.*, {$haversine} AS distance", [$lat, $lng, $lat])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude');
    }

    public function scopeWithinRadius($query, float $radiusKm)
    {
        return $query->having('distance', '<=', $radiusKm)
            ->orderBy('distance');
    }



    public function isOpenNow(): bool
    {
        $now = now();
        $open = Carbon::parse($this->opening_time);
        $close = Carbon::parse($this->closing_time);

       
        if ($close->lessThan($open)) {
            return $now->greaterThanOrEqualTo($open)
                || $now->lessThanOrEqualTo($close);
        }

        return $now->between($open, $close);
    }

}
