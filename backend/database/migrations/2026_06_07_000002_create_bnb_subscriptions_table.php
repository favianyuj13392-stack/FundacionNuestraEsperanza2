<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabla que almacena una suscripción de domiciliación generada via BNB.
     * Un cliente puede tener múltiples suscripciones (ej. una por año o programa).
     * Referencia: POST /DirectDebit/api/Services/GetQRFixedAmount
     *             POST /DirectDebit/api/Services/GetQRVariableAmount
     */
    public function up(): void
    {
        Schema::create('bnb_subscriptions', function (Blueprint $table) {
            $table->id();

            // Relación con nuestra tabla de clientes BNB
            $table->foreignId('bnb_client_id')
                ->constrained('bnb_clients')
                ->cascadeOnDelete();

            // --- Campos devueltos por BNB al generar el QR ---
            $table->string('qr_id')->unique()->nullable(); // ID único del QR asignado por BNB
            $table->longText('qr_image_base64')->nullable(); // qrContent (imagen jpeg en base64)
            $table->string('mime_type')->default('image/jpeg')->nullable();

            // --- Campos enviados al BNB para generar el QR ---
            // Tipo: 1=Monto fijo, 2=Monto variable
            $table->tinyInteger('qr_type')->default(1);

            // Moneda: 1=BOB, 2=USD (para donaciones siempre será 1)
            $table->tinyInteger('currency_code')->default(1);

            $table->decimal('amount', 12, 2); // Monto de la cuota (0 si es variable)
            $table->string('service_code', 20); // Código de servicio asignado por el banco
            $table->string('reference', 60);    // Descripción legible de la suscripción

            // Plan de pagos
            $table->smallInteger('installments_quantity')->default(1);
            $table->tinyInteger('payment_frequency')->default(3); // 3=Mensual
            $table->date('due_date')->nullable();                  // Vencimiento del QR
            $table->dateTime('scheduled_date')->nullable();        // Fecha de primer cobro

            // --- Estado de la suscripción en nuestra plataforma ---
            // 'pending'  : QR generado, esperando que el cliente lo escanee
            // 'enrolled' : Cliente escaneó y aprobó el QR, domiciliación activa
            // 'cancelled': Cancelada (por nosotros o por el cliente)
            $table->string('status')->default('pending');

            // Notas u observaciones internas
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes(); // Permite cancelaciones sin borrar el registro
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bnb_subscriptions');
    }
};
