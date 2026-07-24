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

            $table->decimal('quantity_grams', 8, 2)->default(100); // always grams, always mass

            $table->text('notes')->nullable();       // "finely chopped", free text like "1 medium onion"

            $table->timestamps();

            $table->index('food_id');
            $table->unique(['menu_item_id', 'food_id']);
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
