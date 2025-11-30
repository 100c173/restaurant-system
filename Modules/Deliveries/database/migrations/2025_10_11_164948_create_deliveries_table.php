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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // نفس المستخدم بعد الموافقة
            $table->string('vehicle_type')->nullable(); // نوع المركبة: سيارة، دراجة، الخ
            $table->string('vehicle_number')->nullable();
            $table->boolean('is_available')->default(true); // هل السائق متاح الآن
            $table->decimal('rating', 3, 2)->default(0); // متوسط تقييمه من المطاعم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
