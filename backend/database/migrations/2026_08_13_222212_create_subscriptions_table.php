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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BOB');
            
            $table->enum('status', ['active', 'paused', 'cancelled', 'failed'])->default('active');
            
            $table->timestamp('next_charge_date')->nullable();
            $table->timestamp('last_charge_date')->nullable();
            
            $table->text('cancellation_reason')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            
            $table->string('reactivation_token')->nullable()->unique();
            $table->timestamp('reactivation_token_expires_at')->nullable();
            
            // New fields requested by user
            $table->string('cybersource_payment_token')->nullable();
            $table->unsignedTinyInteger('failed_attempts_count')->default(0);

            $table->timestamps();
            
            // Indexes for better performance
            $table->index('status');
            $table->index('next_charge_date');
            $table->index('reactivation_token');
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
