<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecipeIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'recipe_id', 'food_id', 'food_form_id', 'measure_unit_id',
        'amount', 'is_added_after_cooking', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'amount'                 => 'decimal:4',
            'is_added_after_cooking' => 'boolean',
            'sort_order'             => 'integer',
        ];
    }
    public function food(): BelongsTo// the ingredient itself
    {
        return $this->belongsTo(Food::class);
    }

    public function recipe(): BelongsTo
    {
        return $this->belongsTo(Recipe::class);
    }

    /** The ingredient food (e.g. brown lentils, raw) — not the dish being built. */
    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function foodForm(): BelongsTo
    {
        return $this->belongsTo(FoodForm::class);
    }

    public function measureUnit(): BelongsTo
    {
        return $this->belongsTo(MeasureUnit::class);
    }

    /** Grams this ingredient contributes, converting via the measure unit's base factor when set. */
    public function gramAmount(): float
    {
        $factor = $this->measureUnit->base_factor;

        return $factor
            ? (float) $this->amount * (float) $factor
            : (float) $this->amount;
    }
}
