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
        Schema::create('order_status_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();

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
            ]);

            $table->enum('changed_by_type', [
                'customer',
                'restaurant',
                'driver',
                'system',
            ]);

            $table->unsignedBigInteger('changed_by_id')->nullable();
            $table->text('note')->nullable();

            $table->timestamp('created_at')->useCurrent();
            //order_status_logs has only created_at — logs are never updated, only inserted
            

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_status_logs');
    }
};
