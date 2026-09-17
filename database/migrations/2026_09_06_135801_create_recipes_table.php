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
            $table->foreignId('food_source_record_id')
                ->unique()
                ->constrained('food_source_records')
                ->cascadeOnDelete();
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
