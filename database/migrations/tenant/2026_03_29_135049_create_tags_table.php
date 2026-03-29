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
        Schema::create('tags', function (Blueprint $table) {
            $table->id();

            // Reference to the central DB's global_tags.id — NOT a real foreign key
            // because it points across databases. Used only for sync purposes.
            $table->unsignedBigInteger('global_tag_id')->nullable();

            $table->string('name');
            $table->enum('type', ['allergen', 'dietary', 'label']);
            $table->string('icon')->nullable();
            $table->string('color', 7)->nullable();

            // is_global = true  → came from super admin, synced automatically
            // is_global = false → created by restaurant owner
            $table->boolean('is_global')->default(false);

            // is_locked = true → owner cannot edit or delete (applies to global tags)
            $table->boolean('is_locked')->default(false);

            $table->timestamps();

            // Used by the sync job to find and update existing global tag copies
            $table->index('global_tag_id');
            $table->index('is_global');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
