<?php

use App\Enums\RecipeStatus;
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
        Schema::create('recipes', function (Blueprint $table) {
            $table->id();
            // The dish itself, stored as a food row with is_recipe = true.
            $table->foreignId('food_id')->unique()->constrained('foods')->cascadeOnDelete();
            $table->unsignedSmallInteger('servings')->default(1);
            $table->decimal('weight_before_cooking_g', 10, 2)->nullable();
            $table->decimal('weight_after_cooking_g', 10, 2)->nullable();
            $table->enum('status', array_column(RecipeStatus::cases(), 'value'))
                ->default(RecipeStatus::DRAFT)
                ->index();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
