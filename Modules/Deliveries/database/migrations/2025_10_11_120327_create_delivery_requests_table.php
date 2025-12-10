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
        Schema::create('delivery_requests', function (Blueprint $table) {
            $table->id();
           
            
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();


            $table->string('phone_number')->nullable();
            $table->string('national_id')->nullable(); // رقم الهوية أو الجواز
            $table->string('city')->nullable();
            $table->string('address')->nullable();

            // صور ومستندات
            $table->string('personal_photo')->nullable();
            $table->string('id_card_front')->nullable();
            $table->string('id_card_back')->nullable();
            $table->string('driving_license')->nullable();

            // بيانات المركبة
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('vehicle_photo')->nullable();
            

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('experience_note')->nullable();
            $table->text('admin_note')->nullable();

            $table->text("cancel_reason")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries_requests');
    }
};
