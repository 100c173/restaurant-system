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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0); // base price
            $table->string('image')->nullable();
            $table->boolean('is_available')->default(true);
            $table->unsignedInteger('position')->default(0);
            $table->unsignedInteger('preparation_time')->nullable(); // in minutes
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_nutritionally_analyzed')->default(false);
            $table->timestamps();
            $table->index(['category_id', 'is_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
