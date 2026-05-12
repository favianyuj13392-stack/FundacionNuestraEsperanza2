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
        Schema::table('alliances', function (Blueprint $table) {
            // Por si acaso no las tienes:
            if (!Schema::hasColumn('alliances', 'url')) $table->string('url')->nullable();
            if (!Schema::hasColumn('alliances', 'logo')) $table->string('logo')->nullable();
            if (!Schema::hasColumn('alliances', 'sort_order')) $table->integer('sort_order')->default(0);
            if (!Schema::hasColumn('alliances', 'is_active')) $table->boolean('is_active')->default(true);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alliances', function (Blueprint $table) {
            //
        });
    }
};
