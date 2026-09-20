<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('food_nutrient_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_source_record_id')->constrained('food_source_records')->cascadeOnDelete();
            $table->foreignId('nutrient_id')->constrained('nutrients')->restrictOnDelete();
            // NULL = not reported. 0 = measured zero. "Trace" / "below detection" = NULL + value_qualifier.
            $table->decimal('amount_per_100g', 14, 6)->nullable();
            $table->string('unit', 12);                       // copy of nutrients.unit at write time (calculation snapshot)
            $table->string('value_qualifier', 16)->nullable(); // trace, below_lod
            // imported | estimated | measured | laboratory | calculated  (string + PHP enum cast)
            $table->string('method', 24)->nullable();
            $table->string('confidence_level', 24)->default('reference')->index();
            $table->unsignedInteger('sample_count')->nullable();
            $table->decimal('min_amount', 14, 6)->nullable();
            $table->decimal('max_amount', 14, 6)->nullable();
            $table->decimal('measurement_uncertainty_pct', 7, 3)->nullable();
            $table->timestamp('sampled_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['food_source_record_id', 'nutrient_id'], 'source_nutrient_unique');
            $table->index(['nutrient_id', 'confidence_level'], 'nutrient_confidence_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('food_nutrient_values');
    }
};
