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
        Schema::create('order_deliveries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('delivery_id'); // بدون FK
            $table->enum('status', ['assigned', 'picked_up', 'delivering', 'delivered', 'cancelled'])->default('assigned');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            // FK فقط إذا كان جدول orders داخل نفس قاعدة البيانات
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_deliveries');
    }
};
