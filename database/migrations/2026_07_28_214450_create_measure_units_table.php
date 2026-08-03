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
            
            // USDA's own measure unit id, where this unit came from USDA (1000 = cup, etc).
            // Nullable so you can add tenant/local-only units (e.g. "رغيف", "ربطة") too.
            $table->unsignedInteger('usda_id')->nullable()->unique();

            $table->string('name_en');
            $table->string('name_ar');
            $table->text('notes')->nullable();

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
