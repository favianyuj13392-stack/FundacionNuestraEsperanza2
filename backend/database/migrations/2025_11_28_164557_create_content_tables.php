<?php

// xxxx_xx_xx_xxxxxx_create_content_tables.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->string('image')->nullable();
            $table->date('publication_date')->default(now());
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('role')->nullable(); // e.g. "Madre de familia"
            $table->text('content');
            $table->string('image')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
        
        // Audit / Logs (Simplified version of what you had)
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('entity', 60);
            $table->string('entity_id', 60);
            $table->enum('action', ['insert', 'update', 'delete']);
            $table->foreignId('user_id')->nullable(); // Actor
            $table->longText('changes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('news');
    }
};