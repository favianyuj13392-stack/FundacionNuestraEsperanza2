<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds IP address, User Agent, and digital consent timestamps for Red Enlace / ASFI 10-year compliance audit.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('subscriptions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('failed_attempts_count');
            }
            if (!Schema::hasColumn('subscriptions', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            if (!Schema::hasColumn('subscriptions', 'accepted_terms_at')) {
                $table->timestamp('accepted_terms_at')->nullable()->after('user_agent');
            }
        });

        Schema::table('donations', function (Blueprint $table) {
            if (!Schema::hasColumn('donations', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            if (!Schema::hasColumn('donations', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
        });

        Schema::table('atc_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('atc_subscriptions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            if (!Schema::hasColumn('atc_subscriptions', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
            if (!Schema::hasColumn('atc_subscriptions', 'accepted_terms_at')) {
                $table->timestamp('accepted_terms_at')->nullable();
            }
        });

        Schema::table('atc_transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('atc_transactions', 'ip_address')) {
                $table->string('ip_address', 45)->nullable();
            }
            if (!Schema::hasColumn('atc_transactions', 'user_agent')) {
                $table->text('user_agent')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent', 'accepted_terms_at']);
        });

        Schema::table('donations', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });

        Schema::table('atc_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent', 'accepted_terms_at']);
        });

        Schema::table('atc_transactions', function (Blueprint $table) {
            $table->dropColumn(['ip_address', 'user_agent']);
        });
    }
};
