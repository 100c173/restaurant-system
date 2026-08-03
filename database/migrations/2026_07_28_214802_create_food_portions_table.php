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
        Schema::create('food_portions', function (Blueprint $table) {
            $table->id();

            // FK to foods.id -- not fdc_id .
            $table->foreignId('food_id')
                ->constrained('foods')
                ->cascadeOnDelete();

            $table->foreignId('measure_unit_id')
                ->constrained('measure_units');

            $table->decimal('amount', 8, 3)->default(1); // e.g. 1, 0.5, 2
            $table->string('modifier')->nullable();      // "cooked", "large", "chopped", "raw"
            $table->decimal('gram_weight', 10, 3);        // resolved weight in grams for `amount` of this unit

            $table->integer('data_points')->default(0);

            $table->timestamps();

            $table->index('food_id');
            // Prevents duplicate portion definitions for the same food/unit/modifier combo
            $table->unique(['food_id', 'measure_unit_id', 'modifier'], 'food_unit_modifier_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_portions');
    }
};
