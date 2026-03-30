<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            // Agregamos columna para el monto del QR
            $table->decimal('amount', 12, 2)->nullable()->after('url');
            
            // Estado del QR para seguimiento
            $table->enum('status', ['generated', 'scanned', 'paid', 'expired'])
                  ->default('generated')
                  ->after('expiration_date');
            
            // Campo para guardar la respuesta cruda o la imagen base64
            $table->longText('bnb_blob')->nullable()->after('status')
                  ->comment('Almacena la imagen Base64 o respuesta JSON del banco');
        });
    }

    public function down(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            $table->dropColumn(['amount', 'status', 'bnb_blob']);
        });
    }
};