<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartModifierSelection extends Model
{
    protected $fillable = [
        'cart_id',
        'modifier_group_id',
        'modifier_group_name',
        'modifier_id',
        'modifier_name',
        'price',
    ];

    protected $connection = 'central';


    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
