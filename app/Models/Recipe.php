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
        'weight_after_cooking_g', 'status', 'notes','food_source_record_id',
    ];

    protected function casts(): array
    {
        return [
            'servings'                => 'integer',
            'weight_before_cooking_g' => 'decimal:2',
            'weight_after_cooking_g'  => 'decimal:2',
            'status'                  => RecipeStatus::class,
        ];
    }


    public function ingredients(): HasMany
    {
        return $this->hasMany(RecipeIngredient::class)->orderBy('sort_order');
    }
    public function sourceRecord(): BelongsTo
    {
        return $this->belongsTo(FoodSourceRecord::class, 'food_source_record_id');
    }
}
