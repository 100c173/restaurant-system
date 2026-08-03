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
        Schema::create('menu_item_ingredients', function (Blueprint $table) {
            
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('food_id'); // → central.foods.id

            // What the owner actually picked, e.g. quantity=2, measure_unit_id -> "cup"
            $table->decimal('quantity', 10, 3);

            $table->unsignedBigInteger('measure_unit_id'); // → central.measure_units.id

            // Which specific central food_portions row resolved the conversion.
            // Null when the unit is a direct mass/volume unit (g/kg/ml/L) needing no lookup.
            $table->unsignedBigInteger('portion_id')->nullable(); // → central.food_portions.id

            // Resolved, authoritative mass in grams -- computed by the conversion
            // service from quantity + measure_unit_id (+ portion_id), used for all
            // nutrient math. Passthrough when the owner picks g/kg directly.
            $table->decimal('quantity_grams', 8, 2)->default(100);

            $table->text('notes')->nullable();       // "finely chopped", free text like "1 medium onion"

            $table->timestamps();

            $table->index('food_id');
            $table->index('measure_unit_id');
            $table->index('portion_id');

            $table->unique(['menu_item_id', 'food_id', 'portion_id'], 'menu_item_food_portion_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_item_ingredients');
    }
};
