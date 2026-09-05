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
        Schema::create('measure_units', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->nullable()->unique(); // g, kg, ml, tbsp, loaf
            $table->string('name_ar'); // غرام، كيلوغرام، ملعقة كبيرة
            $table->string('name_en')->nullable();
            $table->string('dimension', 24)->nullable()->index(); // mass, volume, count, household
            $table->decimal('base_factor', 14, 6)->nullable(); // only for fixed base-unit conversions
            $table->boolean('is_system')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('measure_units');
    }
};
