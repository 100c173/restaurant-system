<?php

use App\Enums\FoodSourceStatus;
use App\Enums\FoodSourceType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('food_source_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('food_id')->constrained('foods')->cascadeOnDelete();
            $table->enum('source_type',  array_column(FoodSourceType::cases(), 'value'))->index(); // usda_fdc, local_reference, restaurant_measurement, lab
            $table->string('source_name')->nullable();
            $table->string('external_ref', 128)->nullable(); // FDC id, lab report number, publication ref
            $table->string('data_type', 64)->nullable(); // Foundation, SR Legacy, Branded, local
            $table->foreignId('food_form_id')->nullable()->constrained('food_forms')->nullOnDelete();
            //$table->string('region_code', 12)->nullable()->index();
           // $table->enum('scope_type', array_column(FoodSourceType::cases(),'value'))->default(FoodSourceType::USDA_FDC)->index(); // USDA , Local, RESTAURANT_MEASUREMENT
           // $table->unsignedBigInteger('scope_id')->nullable()->index();
            $table->enum('status', array_column(FoodSourceStatus::cases(),'value'))->default(FoodSourceStatus::ACTIVE)->index(); // active, superseded, rejected
            $table->unsignedSmallInteger('priority')->default(100)->index(); // lower wins in source policy
            $table->boolean('is_preferred')->default(false)->index();
            $table->timestamp('valid_from')->nullable()->index();
            $table->timestamp('valid_to')->nullable()->index();
            $table->timestamp('imported_at')->nullable();
            $table->string('payload_hash', 64)->nullable()->index();
           // $table->json('source_payload')->nullable(); // immutable import evidence, never user-edited
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['food_id', 'source_type', 'external_ref'], 'food_source_external_unique');
            $table->index(['food_id', 'status', 'priority'], 'food_source_resolution_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('food_source_records');
    }
};
