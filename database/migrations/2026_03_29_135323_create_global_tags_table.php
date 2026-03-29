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
        Schema::create('global_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // Contains Gluten, Vegan, Halal…
            $table->enum('type', ['allergen', 'dietary', 'label']);
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable();         // hex color, consistent across all apps
            $table->boolean('is_active')->default(true);    // soft-disable without breaking pivot rows
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('global_tags');
    }
};
