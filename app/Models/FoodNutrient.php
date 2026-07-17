<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodNutrient extends Model
{
    protected $connection = 'central';

    protected $table = 'food_nutrients';

    protected $fillable = [
        'food_id',
        'nutrient_id',
        'nutrient_name',
        'unit',
        'amount',
    ];

    protected $casts = [
        'food_id' => 'integer',
        'nutrient_id' => 'integer',
        'amount' => 'decimal:4',
    ];

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }
}
