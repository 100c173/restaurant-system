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
        Schema::create('food_nutrients', function (Blueprint $table) {
            $table->id();
            // FK to foods.id -- NOT fdc_id. Deleting a food cascades to its nutrients.
            $table->foreignId('food_id')
                ->constrained('foods')
                ->cascadeOnDelete();

            // USDA's own nutrient id (e.g. 1008 = Energy, 1003 = Protein, ...)
            // Stable across the whole FDC database, so it doubles as a stable
            // "nutrient type" key without needing a separate nutrients table.
            $table->unsignedInteger('nutrient_id');

            $table->string('nutrient_name');   // e.g. "Protein", "Vitamin C, total ascorbic acid"
            $table->string('unit', 10);        // e.g. "g", "mg", "µg", "kcal"
            $table->decimal('amount', 10, 4)->nullable(); // per 100g, matching USDA convention

            $table->timestamps();

            // One row per (food, nutrient) -- prevents duplicate nutrient entries per food
            $table->unique(['food_id', 'nutrient_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_nutrients');
    }
};
