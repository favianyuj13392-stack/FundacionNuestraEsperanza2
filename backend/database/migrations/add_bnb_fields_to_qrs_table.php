<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            $table->string('external_qr_id')->nullable()->index()->after('code')->comment('BNB ID matching qrId');
            $table->string('voucher_id')->nullable()->after('external_qr_id');
            $table->string('gloss')->nullable()->after('voucher_id');
            $table->string('donor_name')->nullable()->after('gloss')->comment('Maps to originName');
            $table->dateTime('payment_date')->nullable()->after('expiration_date');
        });
    }

    public function down(): void
    {
        Schema::table('qrs', function (Blueprint $table) {
            $table->dropColumn(['external_qr_id', 'voucher_id', 'gloss', 'donor_name', 'payment_date']);
        });
    }
};
