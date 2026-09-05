<?php

use App\Enums\ConfidenceLevel;
use App\Enums\NutrientValueMethod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('food_nutrient_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_source_record_id')->constrained('food_source_records')->cascadeOnDelete();
            $table->foreignId('nutrient_id')->constrained('nutrients')->restrictOnDelete();
            $table->decimal('amount_per_100g', 14, 6)->nullable();
            $table->string('unit', 12); // copied intentionally for immutable calculation snapshots
            $table->enum('method', array_column(NutrientValueMethod::cases(),'value'))->nullable(); // imported, estimated, measured, laboratory
            $table->enum('confidence_level', array_column(ConfidenceLevel::cases(),'value'))->default(ConfidenceLevel::REFERENCE)->index();
            $table->timestamp('sampled_at')->nullable();
            $table->decimal('measurement_uncertainty_pct', 7, 3)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['food_source_record_id', 'nutrient_id'], 'source_nutrient_unique');
            $table->index(['nutrient_id', 'confidence_level'], 'nutrient_confidence_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_nutrient_values');
    }
};
