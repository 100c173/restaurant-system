<?php

namespace Modules\Restaurants\Services;

use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemIngredient;

/**
 * Simple on purpose — no validation logic in here ,
 *  because the Filament form already validates before this is ever called. T
 *  he service's only job is "given clean data, persist it."
 */
class MenuItemIngredientService
{
    public function add(MenuItem $menuItem, array $data): MenuItemIngredient
    {
        return $menuItem->ingredients()->create([
            'food_id' => $data['food_id'],
            'quantity_grams' => $data['quantity_grams'] ?? 100,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function update(MenuItemIngredient $ingredient, array $data): MenuItemIngredient
    {
        $ingredient->update([
            'food_id' => $data['food_id'],
            'quantity_grams' => $data['quantity_grams'] ?? 100,
            'notes' => $data['notes'] ?? null,
        ]);

        return $ingredient;
    }

    public function remove(MenuItemIngredient $ingredient): void
    {
        $ingredient->delete();
    }
}
