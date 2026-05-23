<?php

namespace Modules\Restaurants\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
// use Modules\Restaurants\Database\Factories\MenuItemModifierFactory;

class MenuItemModifier extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['menu_item_id', 'modifier_group_id', 'modifier_id', 'price_override', 'is_available'];

    protected $table = 'menu_item_modifier_group_modifiers';
    protected $casts = [
        'price_override' => 'decimal:2',
        'is_available' => 'boolean',
    ];
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class);
    }

    public function modifierGroup(): BelongsTo
    {
        return $this->belongsTo(ModifierGroup::class);
    }

    public function modifier(): BelongsTo
    {
        return $this->belongsTo(Modifier::class);
    }

    // protected static function newFactory(): MenuItemModifierFactory
    // {
    //     // return MenuItemModifierFactory::new();
    // }
}
