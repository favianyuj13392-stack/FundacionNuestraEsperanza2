<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla que espeja los campos requeridos por el endpoint UpdateRecord del BNB.
     * Referencia: POST /DirectDebit/api/Services/UpdateRecord
     */
    public function up(): void
    {
        Schema::create('bnb_clients', function (Blueprint $table) {
            $table->id();

            // Relación opcional con un usuario registrado en nuestra plataforma
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Campos requeridos por el BNB en UpdateRecord
            $table->string('identifier')->unique(); // Código único del cliente (ej. CI o slug generado)
            $table->string('name');                  // Nombre completo
            $table->string('address')->nullable();   // Dirección del cliente
            $table->string('email');                 // Correo electrónico
            $table->string('phone_number')->nullable(); // Número de teléfono

            // Estado del cliente en el BNB: 1=Activo, 2=Inactivo
            $table->tinyInteger('bnb_status')->default(1);

            // Control de sincronización con BNB
            $table->boolean('synced_to_bnb')->default(false);
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bnb_clients');
    }
};
