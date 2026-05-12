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
        Schema::create('advertisements', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable(); // Breve descripción
            $table->longText('content'); // Texto enriquecido (Banner o detalles)
            $table->string('image')->nullable(); // ID de Curator para el arte del anuncio
            $table->string('link_url')->nullable(); // Por si el anuncio lleva a algún lado externo
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable(); // Fecha inicio
            $table->timestamp('ends_at')->nullable();   // Fecha fin (opcional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advertisements');
    }
};
