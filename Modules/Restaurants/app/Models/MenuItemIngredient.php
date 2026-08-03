<?php

namespace Modules\Restaurants\Models;

use App\Models\Food;
use App\Models\MeasureUnit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Restaurants\Database\Factories\MenuItemIngredientFactory;

class MenuItemIngredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_item_id',
        'food_id',
        'measure_unit_id',
        'quantity',
        'quantity_grams',
        'notes',
    ];

    protected $casts = [
        'quantity_grams' => 'decimal:2',
        'quantity' => 'decimal:2',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id');
    }

    public function measureUnit(){
        return $this->belongsTo(MeasureUnit::class,'measure_unit_id');
    }

}
