<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Food extends Model
{
    protected $fillable = [
        'fdc_id', 'name_ar', 'name_en', 'food_category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(FoodCategory::class, 'food_category_id');
    }

    public function sourceRecords(): HasMany
    {
        return $this->hasMany(FoodSourceRecord::class);
    }

    public function aliases(): HasMany
    {
        return $this->hasMany(FoodAlias::class);
    }

    public function portions(): HasMany
    {
        return $this->hasMany(FoodPortion::class);
    }
    /** Every nutrient value across all of this food's source records, regardless of which source "wins". */
    public function nutrientValues(): HasManyThrough
    {
        return $this->hasManyThrough(FoodNutrientValue::class, FoodSourceRecord::class);
    }
}
