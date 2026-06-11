<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alliances', function (Blueprint $table) {
            // Cambiamos la columna logo para que acepte nulos
            $table->string('logo')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('alliances', function (Blueprint $table) {
            $table->string('logo')->nullable(false)->change();
        });
    }
};