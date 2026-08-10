<?php

namespace Modules\Restaurants\Services;

use App\Models\Food;
use App\Models\FoodNutrient;
use App\Models\FoodPortion;
use Modules\Restaurants\Models\MenuItem;
use Modules\Restaurants\Models\MenuItemAnalysis;
use Modules\Restaurants\Models\MenuItemIngredient;

class MenuItemIngredientService
{
    /**
     * Maps each stored nutrient column to the USDA nutrient_id(s) that can
     * fill it, in priority order. sugars_total_g tries 2000 first, then
     * falls back to 1063 -- per your spec.
     */
    private const NUTRIENT_PRIORITY = [
        'energy_kcal' => [1008],
        'protein_g' => [1003],
        'fat_total_g' => [1004],
        'carbs_g' => [1005],
        'fiber_g' => [1079],
        'sugars_total_g' => [2000, 1063],
        'calcium_mg' => [1087],
        'iron_mg' => [1089],
        'sodium_mg' => [1093],
        'potassium_mg' => [1092],
        'vitamin_c_mg' => [1162],
        'vitamin_a_rae_ug' => [1106],
    ];

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
        $menuItem = $record->menuItem;
        $record->delete();

        //Reanalysis
        $this->analyze($menuItem);

    }

    /**
     * Calculates total nutrients for the meal and stores/overwrites the
     * MenuItemAnalysis row for this menu item.
     */
    public function analyze(MenuItem $menuItem): MenuItemAnalysis
    {
        $ingredients = MenuItemIngredient::query()
            ->where('menu_item_id', $menuItem->id)
            ->with('food')
            ->get();

        if ($ingredients->isEmpty()) {
            throw new \RuntimeException('Cannot analyze a meal with no ingredients.');
        }

        $foodIds = $ingredients->pluck('food_id')->unique()->values();
        $allNutrientIds = collect(self::NUTRIENT_PRIORITY)->flatten()->unique()->values();

        // One query for all foods' nutrients, grouped for O(1) lookup below.
        $nutrientsByFood = FoodNutrient::query()
            ->whereIn('food_id', $foodIds)
            ->whereIn('nutrient_id', $allNutrientIds)
            ->get()
            ->groupBy('food_id')
            ->map(fn ($rows) => $rows->keyBy('nutrient_id'));
        
        $totals = array_fill_keys(array_keys(self::NUTRIENT_PRIORITY), null);
        $warnings = [];
        $totalGrams = 0.0;

        foreach ($ingredients as $ingredient) {
            $grams = (float) $ingredient->quantity_grams;
            $totalGrams += $grams;

            $foodNutrients = $nutrientsByFood->get($ingredient->food_id, collect());
            
            $foodName = $ingredient->food?->name_ar ?? "Food #{$ingredient->food_id}";
           
            foreach (self::NUTRIENT_PRIORITY as $column => $nutrientIds) {
                $amountPer100g = $this->resolveAmount($foodNutrients, $nutrientIds);

                if ($amountPer100g === null) {
                    $warnings[$column][] = $foodName;
                    continue;
                }

                $contribution = $amountPer100g * $grams / 100;
                $totals[$column] = ($totals[$column] ?? 0) + $contribution;
            }
        }

        foreach ($totals as $column => $value) {
            if ($value !== null) {
                $totals[$column] = round($value, 3);
            }
        }

        foreach ($warnings as $column => $foods) {
            $warnings[$column] = array_values(array_unique($foods));
        }

        return MenuItemAnalysis::updateOrCreate(
            ['menu_item_id' => $menuItem->id],
            array_merge($totals, [
                'total_grams' => round($totalGrams, 2),
                'warnings' => $warnings ?: null,
            ])
        );
    }

    /**
     * Returns the per-100g amount for the first available nutrient_id in
     * priority order, or null if none of them are present for this food.
     */
    private function resolveAmount($foodNutrients, array $nutrientIds): ?float
    {
        foreach ($nutrientIds as $nutrientId) {
            $row = $foodNutrients->get($nutrientId);

            if ($row !== null && $row->amount !== null) {
                return (float) $row->amount;
            }
        }

        return null;
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