<?php

// xxxx_xx_xx_xxxxxx_create_donations_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donors', function (Blueprint $table) {
            $table->id();
            // Link to system user if they registered
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            
            // Contact info (duplicated strictly for historical record or guest donors)
            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('email')->index();
            $table->string('phone', 30)->nullable();
            $table->char('country', 2)->nullable(); // ISO country code
            $table->string('city', 100)->nullable();
            $table->boolean('marketing_opt_in')->default(false);
            $table->timestamps();
        });

        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date')->useCurrent();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns');
            $table->foreignId('donor_id')->nullable()->constrained('donors');
            $table->foreignId('qr_id')->nullable()->constrained('qrs');
            $table->foreignId('currency_id')->constrained('currencies');
            
            $table->decimal('amount', 12, 2);
            $table->enum('status', ['pending', 'succeeded', 'failed', 'refunded', 'canceled'])->default('pending');
            
            // Payment Gateway info
            $table->string('provider', 40); // stripe, paypal, manual
            $table->string('provider_payment_id')->nullable();
            $table->string('provider_subscription_id')->nullable();
            
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_anonymous')->default(false);
            
            $table->decimal('fee', 12, 2)->nullable();
            $table->decimal('net_amount', 12, 2)->nullable();
            
            $table->string('channel', 40)->nullable(); // web, app, in-person
            $table->string('ip', 45)->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_payment_id']);
        });
        
        // Certificates linked to donations
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('donation_id')->unique()->constrained('donations')->cascadeOnDelete();
            $table->string('folio', 50)->unique();
            $table->string('pdf_url');
            $table->timestamp('issued_on')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('donations');
        Schema::dropIfExists('donors');
    }
};