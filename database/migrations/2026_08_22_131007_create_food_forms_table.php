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
        Schema::create('food_forms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique(); // raw, boiled, grilled, fried, strained
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('group', 32)->nullable()->index(); // state, cooking_method, edible_part
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_forms');
    }
};
