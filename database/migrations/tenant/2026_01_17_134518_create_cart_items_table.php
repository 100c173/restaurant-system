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
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            // user_id بدون FK لأنه في قاعدة بيانات مختلفة
            $table->unsignedBigInteger('user_id');
            $table->foreignId('menu_item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('price', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'menu_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cart_items');
    }
};
