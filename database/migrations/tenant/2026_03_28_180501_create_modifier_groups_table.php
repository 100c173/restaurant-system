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
        Schema::create('modifier_groups', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // "Choose your sauce", "Add toppings"
            $table->boolean('is_required')->default(false);
            $table->boolean('is_multiple')->default(false); // single vs multi-select
            $table->unsignedInteger('min_selections')->default(0);
            $table->unsignedInteger('max_selections')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modifier_groups');
    }
};
