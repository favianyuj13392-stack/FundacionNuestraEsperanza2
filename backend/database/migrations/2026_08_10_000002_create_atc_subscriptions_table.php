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
        Schema::create('atc_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_profile_id')->constrained('atc_payment_profiles')->cascadeOnDelete();
            
            // Mandatory FK additions requested by IA FUNDACIÓN for fund consolidation
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();

            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BOB');
            $table->unsignedTinyInteger('billing_day')->default(1)->comment('Día del mes para cobro (1-28)');
            $table->enum('status', ['pending', 'active', 'paused', 'cancelled', 'failed'])->default('pending');
            
            $table->timestamp('next_billing_at')->nullable();
            $table->timestamp('last_billed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'next_billing_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atc_subscriptions');
    }
};
