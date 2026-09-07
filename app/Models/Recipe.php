<?php

namespace App\Models;

use App\Enums\RecipeStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Recipe extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_id', 'servings', 'weight_before_cooking_g',
        'weight_after_cooking_g', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'servings' => 'integer',
            'weight_before_cooking_g' => 'decimal:2',
            'weight_after_cooking_g' => 'decimal:2',
            'status' => RecipeStatus::class,
        ];
    }

    /** The dish itself, as a food row (name, category, and where its own source
     * records/nutrient values get stored once totals are calculated and saved). */
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }
}
