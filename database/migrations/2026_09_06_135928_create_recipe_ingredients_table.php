<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('recipe_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignId('food_id')->constrained('foods')->restrictOnDelete(); // the ingredient
            $table->foreignId('food_form_id')->nullable()->constrained('food_forms')->nullOnDelete();
            $table->foreignId('measure_unit_id')->constrained('measure_units')->restrictOnDelete();
            $table->decimal('amount', 12, 4);
            // True for ingredients added after cooking is complete (e.g. finishing oil),
            // so they're excluded from the pre-cook weight and can be shown/toggled separately.
            $table->boolean('is_added_after_cooking')->default(false)->index();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['recipe_id', 'sort_order'], 'recipe_ingredient_order_index');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_ingredients');
    }
};
