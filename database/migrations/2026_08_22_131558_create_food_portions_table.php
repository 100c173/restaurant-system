<?php

use App\Enums\ConfidenceLevel;
use App\Enums\PortionBasis;
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
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->foreignId('measure_unit_id')->constrained('measure_units')->restrictOnDelete();
            $table->decimal('amount', 12, 4)->default(1);
            $table->decimal('gram_weight', 14, 6);
            $table->foreignId('food_source_record_id')->nullable()
                ->constrained('food_source_records')->nullOnDelete();
            $table->foreignId('food_form_id')->nullable()
                ->constrained('food_forms')->nullOnDelete();
            $table->enum('basis', array_column(PortionBasis::cases(),'value'))->default(PortionBasis::REFERENCE)->index();//reference , usda , measured , estimated , restaurant_measured
            $table->enum('confidence_level', array_column(ConfidenceLevel::cases(),'value'))->default(ConfidenceLevel::REFERENCE)->index(); //calculated , reference , local_reference , measured , reviewed , verified
            //$table->string('region_code', 12)->nullable()->index();
            $table->boolean('is_default')->default(false)->index();
            $table->timestamp('valid_from')->nullable()->index();
            $table->timestamp('valid_to')->nullable()->index();
            $table->json('evidence')->nullable();
            $table->timestamps();

            $table->index(['food_id', 'measure_unit_id', 'food_form_id'], 'food_portion_resolution_index');
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
