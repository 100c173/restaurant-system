<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodPortion extends Model
{
    protected $connection = 'central' ;

    protected $fillable =[
        'food_id',
        'measure_unit_id',
        'amount',
        'modifier',
        'gram_weight',
        'data_points',
    ];

    protected $casts =[
        'amount'=>'decimal:4',
        'gram_weight'=>'decimal:3',
    ];

    public function food():BelongsTo{
        return $this->belongsTo(Food::class);
    }

    public function measureUnit():BelongsTo{
        return $this->belongsTo(MeasureUnit::class);
    }
}
