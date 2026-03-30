<?php

// xxxx_xx_xx_xxxxxx_create_campaigns_and_programs_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->string('type', 30)->nullable();
            $table->text('description')->nullable();
            // Foreign key to currencies
            $table->foreignId('currency_id')->constrained('currencies'); 
            $table->decimal('monetary_goal', 12, 2)->default(0);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['draft', 'active', 'finished', 'hidden'])->default('active');
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image')->nullable(); // Added based on your request
            $table->string('color')->default('bg-celeste-fondo'); // Added based on your request
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        
         // QRs table (linked to campaigns)
        Schema::create('qrs', function (Blueprint $table) {
            $table->id();
            $table->string('code', 60)->unique();
            $table->string('url');
            $table->timestamp('creation_date')->useCurrent();
            $table->timestamp('expiration_date')->nullable();
            $table->string('description')->nullable();
            $table->foreignId('campaign_id')->nullable()->constrained('campaigns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('qrs');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('campaigns');
    }
};