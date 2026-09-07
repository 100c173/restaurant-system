<?php

namespace App\Models;

use App\Enums\ConfidenceLevel;
use App\Enums\PortionBasis;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FoodPortion extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_id', 'measure_unit_id', 'amount', 'gram_weight',
        'food_source_record_id', 'food_form_id', 'basis', 'confidence_level',
        'is_default', 'valid_from', 'valid_to', 'evidence',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:4',
            'gram_weight' => 'decimal:6',
            'basis' => PortionBasis::class,
            'confidence_level' => ConfidenceLevel::class,
            'is_default' => 'boolean',
            'valid_from' => 'datetime',
            'valid_to' => 'datetime',
            'evidence' => 'array',
        ];
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function measureUnit(): BelongsTo
    {
        return $this->belongsTo(MeasureUnit::class);
    }

    public function foodSourceRecord(): BelongsTo
    {
        return $this->belongsTo(FoodSourceRecord::class);
    }

    public function foodForm(): BelongsTo
    {
        return $this->belongsTo(FoodForm::class);
    }
}
