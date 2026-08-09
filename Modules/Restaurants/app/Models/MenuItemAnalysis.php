<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Restaurants\Database\Factories\MenuItemAnalysisFactory;

class MenuItemAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id',
        'total_grams',
        'energy_kcal',
        'protein_g',
        'fat_total_g',
        'carbs_g',
        'fiber_g',
        'sugars_total_g',
        'calcium_mg',
        'iron_mg',
        'sodium_mg',
        'potassium_mg',
        'vitamin_c_mg',
        'vitamin_a_rae_ug',
        'warnings',
    ];

    protected $casts = [
        'total_grams' => 'decimal:2',
        'energy_kcal' => 'decimal:3',
        'protein_g' => 'decimal:3',
        'fat_total_g' => 'decimal:3',
        'carbs_g' => 'decimal:3',
        'fiber_g' => 'decimal:3',
        'sugars_total_g' => 'decimal:3',
        'calcium_mg' => 'decimal:3',
        'iron_mg' => 'decimal:3',
        'sodium_mg' => 'decimal:3',
        'potassium_mg' => 'decimal:3',
        'vitamin_c_mg' => 'decimal:3',
        'vitamin_a_rae_ug' => 'decimal:3',
        'warnings' => 'array',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }
}
