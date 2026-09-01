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
        Schema::create('atc_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->nullable()->constrained('atc_subscriptions')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            
            // Mandatory FK additions requested by IA FUNDACIÓN
            $table->foreignId('campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('program_id')->nullable()->constrained()->nullOnDelete();

            $table->string('cybersource_request_id')->nullable()->index();
            $table->string('merchant_reference_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('BOB');
            
            $table->enum('status', ['PENDING', 'AUTHORIZED', 'CAPTURED', 'FAILED', 'CANCELLED'])->default('PENDING');
            $table->enum('flow_type', ['CIT_3DS', 'MIT_RECURRING'])->default('CIT_3DS');
            
            $table->string('eci_raw', 30)->nullable()->comment('05, 02, 06, 01, 07, 00, internet, vbv, spa');
            $table->string('cavv_raw')->nullable()->comment('Cryptogram');
            $table->string('3ds_version', 10)->nullable();
            
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atc_transactions');
    }
};
