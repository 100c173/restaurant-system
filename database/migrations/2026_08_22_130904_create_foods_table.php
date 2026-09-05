<?php

use App\Enums\FoodSourceStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('foods', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('fdc_id')->nullable()->unique();
           // $table->string('local_code', 64)->nullable()->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->foreignId('food_category_id')->nullable()->constrained('food_categories')->nullOnDelete();
            //$table->foreignId('canonical_food_id')->nullable()->constrained('foods')->nullOnDelete();
            //$table->enum('status', array_column(FoodSourceStatus::cases(), 'value'))->default(FoodSourceStatus::ACTIVE)->index();
           // $table->string('region_code', 12)->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('foods');
    }
};
