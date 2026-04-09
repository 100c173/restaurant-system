<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Free / Basic / Pro
            $table->string('slug')->unique(); // free, basic, pro

            $table->decimal('price', 10, 2)->default(0); // 0 free plan
            $table->string('interval')->default('monthly'); // monthly / yearly

            $table->integer('trial_days')->default(0);
            $table->json('features')->nullable();
             $table->boolean('is_active')->default(true);
             
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
