<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Food extends Model
{
    protected $connection = 'central';
    protected $table = 'foods';

    protected $fillable = [
        'fdc_id',
        'name_ar',
        'name_en',
        'description',
        'data_type',
        'category',
    ];

    protected $casts = [
        'fdc_id' => 'integer',
    ];

    public function nutrients(): HasMany
    {
        return $this->hasMany(FoodNutrient::class);
    }
}
