<?php

namespace App\Models;

use App\Enums\ConfidenceLevel;
use App\Enums\NutrientValueMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodNutrientValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_source_record_id', 'nutrient_id', 'amount_per_100g', 'unit',
        'method', 'confidence_level', 'sampled_at',
        'measurement_uncertainty_pct', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_per_100g' => 'decimal:6',
            'method' => NutrientValueMethod::class,
            'confidence_level' => ConfidenceLevel::class,
            'sampled_at' => 'datetime',
            'measurement_uncertainty_pct' => 'decimal:3',
        ];
    }

    public function foodSourceRecord(): BelongsTo
    {
        return $this->belongsTo(FoodSourceRecord::class);
    }

    public function nutrient(): BelongsTo
    {
        return $this->belongsTo(Nutrient::class);
    }
}
