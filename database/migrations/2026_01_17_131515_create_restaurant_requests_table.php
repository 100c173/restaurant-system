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
        Schema::create('restaurant_requests', function (Blueprint $table) {
            $table->id();
            $table->string('owner_name');
            $table->string("owner_email");
            $table->string("owner_phone");
            $table->string("restaurant_name");
            $table->string("restaurant_email")->nullable();
            $table->string("restaurant_phone")->nullable();
            $table->string("address");
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('cancel_resone')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_requests');
    }
};
