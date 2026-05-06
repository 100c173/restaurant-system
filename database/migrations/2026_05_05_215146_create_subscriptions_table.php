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
        Schema::create('subscriptions', function (Blueprint $table) {

            $table->id();

            $table->string('tenant_id');
            $table->foreign('tenant_id')
                ->references('id')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreignId('plan_id')
                ->constrained()
                ->restrictOnDelete();

            // Copied from plan at creation time, never changes with plan edits
            $table->decimal('price', 10, 2)->default(0);
            $table->enum('billing_interval', ['daily', 'weekly', 'monthly', 'yearly', 'lifetime'])
                ->default('monthly');

            $table->enum('status', ['trial', 'active', 'past_due', 'cancelled', 'expired'])
                ->default('trial')
                ->index();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->string('payment_reference')->nullable();
            $table->text('notes')->nullable();

            $table->foreignId('activated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('activated_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
