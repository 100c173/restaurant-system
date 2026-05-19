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
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            // Keep as string — this is a cross-DB soft reference
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('variant_id');

            // Denormalize a price snapshot at add-time (critical for orders)
            $table->decimal('unit_price', 10, 2);

            // Denormalize name for resilience (item could be renamed/deleted)
            $table->string('item_name');
            $table->string('variant_name');

            $table->string('description')->nullable();
            $table->integer('quantity')->default(0);

            $table->timestamps();

            // Prevent duplicate item rows per user per tenant
            $table->unique(['user_id', 'tenant_id', 'item_id','variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
