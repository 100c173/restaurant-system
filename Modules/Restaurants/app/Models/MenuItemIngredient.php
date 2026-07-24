<?php

namespace Modules\Restaurants\Models;

use App\Models\Food;
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
        'quantity_grams',
        'notes',
    ];

    protected $casts = [
        'quantity_grams' => 'decimal:2',
    ];

    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    // Food lives on the 'central' connection — Eloquent handles this
    // transparently, it just runs a second query on that connection.
    public function food(): BelongsTo
    {
        return $this->belongsTo(Food::class, 'food_id');
    }
}
