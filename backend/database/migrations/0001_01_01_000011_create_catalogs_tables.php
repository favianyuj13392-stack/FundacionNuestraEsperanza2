// xxxx_xx_xx_xxxxxx_create_catalogs_tables.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Currencies
        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique();
            $table->char('iso_code', 3)->unique(); // USD, BOB, EUR
            $table->string('symbol', 10)->nullable();
        });

        // Availabilities (for volunteers)
        Schema::create('availabilities', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // morning, afternoon
            $table->string('label', 60);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('availabilities');
    }
};