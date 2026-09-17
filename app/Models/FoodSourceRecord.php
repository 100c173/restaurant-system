<?php
namespace App\Models;

use App\Enums\Country;
use App\Enums\FoodSourceStatus;
use App\Enums\FoodSourceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FoodSourceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'food_id', 'source_type', 'source_name', 'external_ref', 'data_type',
        'food_form_id', 'status', 'priority', 'is_preferred',
        'valid_from', 'valid_to', 'imported_at', 'payload_hash', 'notes', 'fdc_id','country'
    ];

    protected function casts(): array
    {
        return [
            'source_type'  => FoodSourceType::class,
            'country'      => Country::class,
            'status'       => FoodSourceStatus::class,
            'priority'     => 'integer',
            'is_preferred' => 'boolean',
            'valid_from'   => 'datetime',
            'valid_to'     => 'datetime',
            'imported_at'  => 'datetime',
        ];
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class);
    }

    public function foodForm(): BelongsTo
    {
        return $this->belongsTo(FoodForm::class);
    }

    public function nutrientValues(): HasMany
    {
        return $this->hasMany(FoodNutrientValue::class);
    }

    public function foodPortions(): HasMany
    {
        return $this->hasMany(FoodPortion::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', FoodSourceStatus::ACTIVE);
    }

    /** Resolution order used to pick which source "wins" for a food: preferred first, then lowest priority number. */
    public function scopeOrderedByPreference(Builder $query): Builder
    {
        return $query->orderByDesc('is_preferred')->orderBy('priority');
    }
    public function recipe(): HasOne
    {
        return $this->hasOne(Recipe::class);
    }
}
