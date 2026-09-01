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
        Schema::create('atc_payment_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_token')->nullable()->comment('Token TMS de Cliente en Cybersource');
            $table->string('payment_instrument_token')->comment('Token de Instrumento de Pago TMS');
            $table->string('card_type', 50)->comment('VISA, MASTERCARD, AMEX');
            $table->string('card_last4', 4)->comment('Últimos 4 dígitos de la tarjeta');
            $table->string('card_expiration_month', 2);
            $table->string('card_expiration_year', 4);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('payment_instrument_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('atc_payment_profiles');
    }
};
