<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Nutrient extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'usda_nutrient_id', 'name_ar', 'name_en', 'unit',
        'display_order', 'is_core', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'is_core' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function foodNutrientValues(): HasMany
    {
        return $this->hasMany(FoodNutrientValue::class);
    }

    /** Core macros/energy shown by default (matches the dish builder's default checked set). */
    public function scopeCore(Builder $query): Builder
    {
        return $query->where('is_core', true);
    }
}
