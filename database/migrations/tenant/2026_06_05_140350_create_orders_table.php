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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Soft reference to central orders — no FK constraint (cross-DB)
            $table->unsignedBigInteger('central_order_id')->unique();
            $table->string('reference_number')->unique();

            $table->enum('status', [
                'pending',
                'confirmed',
                'rejected',
                'preparing',
                'ready',
            ])->default('pending');

            $table->enum('type', ['delivery', 'pickup', 'dine_in']);
            $table->string('table_number')->nullable();

            // Customer snapshot
            $table->string('customer_name');
            $table->string('customer_phone');

            $table->text('special_instructions')->nullable();

            // Financials
            $table->decimal('subtotal', 10, 2);
            $table->decimal('total', 10, 2);

            // Timing
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ready_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
