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
        Schema::create('cart_modifier_selection', function (Blueprint $table) {
            $table->id();

            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();

            // Modifier group (snapshot + reference)
            $table->unsignedBigInteger('modifier_group_id');
            $table->string('modifier_group_name');

            // Modifier (snapshot + reference)
            $table->unsignedBigInteger('modifier_id');
            $table->string('modifier_name');


            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();

            // Prevent duplicate selections within the same cart item
            $table->unique([
                'cart_id',
                'modifier_group_id',
                'modifier_id'
            ]);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_modifier_selection');
    }
};
