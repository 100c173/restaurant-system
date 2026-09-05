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
        Schema::create('nutrients', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique(); // energy_kcal, protein_g, iron_mg
            $table->unsignedInteger('usda_nutrient_id')->nullable()->unique();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('unit', 12); // kcal, g, mg, ug
            //$table->string('category', 32)->index(); // macro, vitamin, mineral, other
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->boolean('is_core')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nutrients');
    }
};
