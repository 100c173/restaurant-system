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
        Schema::create('food_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->string('search_normalized')->index(); // pre-normalized Arabic search key
            $table->string('dialect', 32)->nullable()->index();
            $table->string('region_code', 12)->nullable()->index();
            $table->boolean('is_preferred')->default(false)->index();
            $table->timestamps();

            $table->unique(['food_id', 'search_normalized', 'region_code'], 'food_alias_scope_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_aliases');
    }
};
