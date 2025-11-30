<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Orders\Models\CartItem;

// use Modules\Restaurants\Database\Factories\MenuItemFactory;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'restaurant_id',
        'name',
        'description',
        'price',
        'image',
        'is_available'
    ];

    protected $casts = [
        'is_available' => 'boolean',
    ];

    public function CartItems():HasMany{
        return $this->hasMany(CartItem::class);
    }

    public function restaurant():BelongsTo{
        return $this->belongsTo(Restaurant::class);
    }
}
