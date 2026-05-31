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
            $table->string('fingerprint');

            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            // Keep as string — this is a cross-DB soft reference
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('variant_id')->nullable();

            // snapshot 
            $table->decimal('unit_price', 10, 2);

            //snapshot at add-time
            $table->string('item_name');
            $table->string('variant_name')->nullable();

            $table->string('description')->nullable();
            $table->integer('quantity')->default(1);

            $table->text('special_note')->nullable();

            $table->timestamps();
            $table->index('fingerprint');
            $table->unique(['user_id', 'tenant_id', 'fingerprint']);

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
