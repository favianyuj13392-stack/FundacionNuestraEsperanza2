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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->enum('allowed_frequencies', ['all', 'monthly_only', 'once_only'])->default('all')->after('status');
            $table->enum('allowed_payment_methods', ['all', 'card_only', 'qr_only'])->default('all')->after('allowed_frequencies');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropColumn(['allowed_frequencies', 'allowed_payment_methods']);
        });
    }
};
