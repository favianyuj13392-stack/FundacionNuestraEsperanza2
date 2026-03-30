<?php

namespace App\Console\Commands;

use App\Jobs\GenerarCertificadoJob;
use App\Models\Donacion;
use App\Models\Donante;
use App\Models\Persona; // Necesario para buscar la persona
use App\Models\TipoMoneda;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestCertificadoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:generar-certificados-varios';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simula múltiples donaciones y despacha Jobs para generar certificados de prueba.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando simulación de generación de certificados para 5 donaciones...');

        try {
            // 1. **MODIFICACIÓN CRÍTICA:** Buscar la Persona de prueba ya creada (panel.test@fundacion.org)
            $persona = Persona::where('correo_electronico', 'panel.test@fundacion.org')->first();

            if (!$persona) {
                $this->error('ERROR: No se encontró la Persona con correo panel.test@fundacion.org. Ejecuta php artisan test:usuario-donante primero.');
                return self::FAILURE;
            }

            // 2. Buscar o crear el Donante vinculado a esa Persona
            $donante = Donante::firstOrCreate(
                ['persona_id' => $persona->id_persona],
                [
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido_paterno, // Usar apellido_paterno como apellido en Donante
                    'email' => $persona->correo_electronico,
                    'telefono' => '99988877'
                ]
            );

            // 3. Simular un Tipo de Moneda (si no existe)
            $moneda = TipoMoneda::firstOrCreate(
                ['codigo' => 'BOB'],
                [
                    'descripcion' => 'Bolivianos',
                    'simbolo' => 'Bs.'
                ]
            );

            $this->line("Donante [{$donante->id_donante}] (vinculado a {$persona->correo_electronico}) y Moneda [{$moneda->codigo}] listos.");
            
            // 4. Crear 5 Donaciones nuevas (con ID consecutivo y monto suficiente)
            
            for ($i = 1; $i <= 5; $i++) {
                // Usamos el ID del donante ENCONTRADO/CREADO
                $donacion = Donacion::create([
                    'id_donante' => $donante->id_donante,
                    'id_campania' => null,
                    'monto' => 350.00 + $i, // Monto suficiente (mayor a 100)
                    'id_tipo_moneda' => $moneda->id_tipo_moneda,
                    'estado' => 'succeeded',
                    'proveedor' => 'test_masivo',
                    'id_pago_proveedor' => 'mass_' . uniqid(),
                    'fecha' => now(),
                ]);

                // 5. Despachar el Job para generar el certificado
                GenerarCertificadoJob::dispatch($donacion);
                $this->line("✅ Donación ID: {$donacion->id_donacion} creada y Job despachado.");
            }

            $this->info('Comando finalizado. Revisa tu worker de colas (queue:work) para ver el procesamiento.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error durante la simulación:');
            $this->error($e->getMessage());
            Log::error('Error en TestCertificadoCommand: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}