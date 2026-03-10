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
        Schema::create('table_images', function (Blueprint $table) {
            $table->id();
            $table->morphs('imageable'); // create imageable_id imageable_type
            $table->string('path');
            $table->enum('type', [
                'logo',
                'profile',
                'gallery'
            ])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_images');
    }
};
