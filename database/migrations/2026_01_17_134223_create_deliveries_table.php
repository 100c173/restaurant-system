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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();

            // المعلومات الأساسية
            $table->foreignId('user_id')->constrained()->onDelete('cascade');


            // بيانات التواصل
            $table->string('phone_number');

            // بيانات السائق
            $table->string('national_id')->nullable();
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            // وثائق شخصية
            $table->string('personal_photo')->nullable();
            $table->string('id_card_front')->nullable();
            $table->string('id_card_back')->nullable();
            $table->string('driving_license')->nullable();

            // بيانات المركبة
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('vehicle_photo')->nullable();

            // حالة السائق داخل النظام (فعال/معطل)
            $table->boolean('is_active')->default(true);

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
