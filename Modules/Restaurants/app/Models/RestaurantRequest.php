<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
// use Modules\Restaurants\Database\Factories\RestaurantRequestFactory;

class RestaurantRequest extends Model
{
    use HasFactory , SoftDeletes ;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = 
    [
        "owner_name",
        "owner_email",
        "restaurant_name",
        "owner_phone",
        "address",
        "status",
    ];

    // protected static function newFactory(): RestaurantRequestFactory
    // {
    //     // return RestaurantRequestFactory::new();
    // }
}
