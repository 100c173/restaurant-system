<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
// use Modules\Restaurants\Database\Factories\CategoryFactory;

class Category extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = 
    [
        'restaurant_id',
        'name',
        'description',
        'position'
    ];

    public function restaurant():BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function menuItems():HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    // protected static function newFactory(): CategoryFactory
    // {
    //     // return CategoryFactory::new();
    // }
}
