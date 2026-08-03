<?php

namespace Modules\Restaurants\Services;

use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemIngredient;
use App\Models\FoodPortion;

class MenuItemIngredientService
{
    public function add(MenuItem $menuItem, array $data): MenuItemIngredient
    {
        return MenuItemIngredient::create($this->resolve($data) + [
            'menu_item_id' => $menuItem->id,
        ]);
    }

    public function update(MenuItemIngredient $record, array $data): MenuItemIngredient
    {
        $record->update($this->resolve($data));

        return $record;
    }

    public function remove(MenuItemIngredient $record): void
    {
        $record->delete();
    }

    /**
     * Resolves the form's food_id/portion_id/quantity into the columns
     * menu_item_ingredients actually stores.
     */
    private function resolve(array $data): array
    {
        $portion = FoodPortion::findOrFail($data['portion_id']);
        $quantity = (float) $data['quantity'];

        return [
            'food_id' => $data['food_id'],
            'quantity' => $quantity,
            'measure_unit_id' => $portion->measure_unit_id,
            'portion_id' => $portion->id,
            'quantity_grams' => round($quantity * (float) $portion->gram_weight, 2),
            'notes' => $data['notes'] ?? null,
        ];
    }
}
