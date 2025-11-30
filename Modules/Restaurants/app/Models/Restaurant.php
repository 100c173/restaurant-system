<?php

namespace Modules\Restaurants\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Orders\Models\Order;

// use Modules\Restaurants\Database\Factories\RestaurantFactory;

class Restaurant extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        "owner_id",
        "name",
        "description",
        "logo",
        "cover_image",
        "address",
        "phone",
        "email",
        "status",
        "commission_rate",
        "is_active",
        "latitude",
        "longitude",
        "opening_time",
        "closing_time",
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function orders(): HasMany{
        return $this->hasMany(Order::class);
    }
}
