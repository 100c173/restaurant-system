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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();

            // Relations
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            // Snapshot
            $table->string('restaurant_name');

            // Type & Status
            $table->enum('type', ['delivery', 'pickup', 'dine_in']);
            $table->enum('status', [
                'pending',
                'confirmed',
                'rejected',
                'preparing',
                'ready',
                'assigned',
                'picked_up',
                'delivered',
                'completed',
                'cancelled',
            ])->default('pending');

            // Payment
            $table->enum('payment_method', ['cash', 'online']);
            $table->enum('payment_status', [
                'pending',
                'paid',
                'failed',
                'refunded',
            ])->default('pending');
            $table->string('payment_reference')->nullable();

            // Financials
            $table->decimal('subtotal', 10, 2);
            $table->decimal('delivery_fee', 10, 2)->nullable();
            $table->decimal('discount_amount', 10, 2)->nullable();
            $table->decimal('total', 10, 2);

            // Delivery
            $table->text('delivery_address')->nullable();
            $table->decimal('delivery_lat', 10, 7)->nullable();
            $table->decimal('delivery_lng', 10, 7)->nullable();
            $table->foreignId('driver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Instructions
            $table->text('special_instructions')->nullable();
            $table->text('payment_code')->nullable();

            // Timestamps for key events
            $table->timestamp('placed_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('delivered_at')->nullable();

            
            
            //All event timestamps (confirmed_at, ready_at etc.) are on orders itself for fast access without querying logs

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
