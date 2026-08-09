<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_item_analyses', function (Blueprint $table) {
            $table->id();
            // One analysis row per menu item -- overwritten on every re-run.
            $table->foreignId('menu_item_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            // Total resolved weight of all ingredients (sum of quantity_grams),
            // for context -- lets you show "per 100g" style comparisons later.
            $table->decimal('total_grams', 10, 2)->default(0);

            // Nutrient totals for the whole meal. Nullable: a meal only shows
            // a value for a nutrient if at least one ingredient reported it.
            $table->decimal('energy_kcal', 10, 3)->nullable();
            $table->decimal('protein_g', 10, 3)->nullable();
            $table->decimal('fat_total_g', 10, 3)->nullable();
            $table->decimal('carbs_g', 10, 3)->nullable();
            $table->decimal('fiber_g', 10, 3)->nullable();
            $table->decimal('sugars_total_g', 10, 3)->nullable();
            $table->decimal('calcium_mg', 10, 3)->nullable();
            $table->decimal('iron_mg', 10, 3)->nullable();
            $table->decimal('sodium_mg', 10, 3)->nullable();
            $table->decimal('potassium_mg', 10, 3)->nullable();
            $table->decimal('vitamin_c_mg', 10, 3)->nullable();
            $table->decimal('vitamin_a_rae_ug', 10, 3)->nullable();

            // Which ingredients/nutrients had incomplete USDA data, e.g.
            // {"iron_mg": ["Onion", "Garlic"]} -- so the UI can flag
            // totals that are undercounted rather than showing them as exact.
            $table->json('warnings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_analyses');
    }
};
