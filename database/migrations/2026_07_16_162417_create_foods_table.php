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
        Schema::create('foods', function (Blueprint $table) {
            $table->id();

            // USDA FoodData Central identifier -- unique, indexed, never duplicated
            // elsewhere; every other table references foods.id, not this column.
            $table->unsignedInteger('fdc_id');

            $table->string('name_ar');                 // Arabic ingredient name
            $table->string('name_en')->nullable();      // English query/description used to find the match
            $table->string('description')->nullable();  // USDA's own food description
            $table->string('data_type')->nullable();     // Foundation / SR Legacy / Branded / etc.
            $table->string('category')->nullable();      // Your own grouping: Grains, Dairy, Spices, etc.
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
