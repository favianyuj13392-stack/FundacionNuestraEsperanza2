<?php

namespace App\Console\Commands;

use App\Jobs\GenerarCertificadoJob;
use App\Models\Donacion;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RegenerarCertificadosCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificado:regenerar-faltantes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recorre donaciones con registro de certificado pero sin archivo físico, y despacha el Job.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Buscando donaciones con certificado registrado pero sin archivo PDF físico...');

        try {
            // 1. Obtener donaciones que tienen un registro de certificado asociado.
            // Usamos ->has('certificado') para obtener solo las donaciones que tienen una fila en 'certificados'.
            $donacionesConRegistro = Donacion::query()
                ->has('certificado') 
                ->with('certificado') // Precargar el certificado
                ->where('estado', 'succeeded') // Solo nos interesan las exitosas
                ->get();
            
            $jobsDispatched = 0;

            if ($donacionesConRegistro->isEmpty()) {
                $this->comment('✅ No se encontraron donaciones con registros de certificado pendientes de archivo físico.');
                return self::SUCCESS;
            }

            $this->line("   -> Se encontraron {$donacionesConRegistro->count()} donaciones con registro de certificado.");

            foreach ($donacionesConRegistro as $donacion) {
                $certificado = $donacion->certificado;

                // 2. Verificar si el archivo PDF EXISTE en el storage público.
                // Si el archivo no existe, significa que el job debe correr.
                if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($certificado->pdf_url)) {
                    
                    // Despachar el Job para que el worker lo genere
                    GenerarCertificadoJob::dispatch($donacion);
                    $jobsDispatched++;
                    $this->line("   -> [Donación ID: {$donacion->id_donacion}] Despachado Job para generar: {$certificado->pdf_url}");

                } else {
                    $this->line("   -> [Donación ID: {$donacion->id_donacion}] Archivo físico ya existe. Saltando.");
                }
            }
            
            $this->line('--------------------------------------------------');
            $this->info("✅ Proceso completado. Se despacharon {$jobsDispatched} jobs a la cola.");
            $this->info("   - ¡Asegúrate de que tu worker de colas (php artisan queue:work) esté corriendo ahora!");
            $this->line('--------------------------------------------------');


            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error durante la regeneración:');
            $this->error($e->getMessage());
            Log::error('Error en RegenerarCertificadosCommand: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}