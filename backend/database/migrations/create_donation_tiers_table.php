<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('donation_tiers', function (Blueprint $table) {
            $table->id();
            $table->decimal('amount', 12, 2);
            $table->string('label', 50); // Ej: "Beca Escolar" o "Donación Básica"
            // Asumiendo que la tabla currencies ya existe según tu SQL
            $table->foreignId('currency_id')->default(2)->constrained('currencies'); 
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0); // Para ordenar los botones visualmente
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('donation_tiers');
    }
};