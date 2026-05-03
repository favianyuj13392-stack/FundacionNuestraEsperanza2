<?php

namespace App\Console\Commands;

use App\Jobs\GenerarCertificadoJob;
use App\Models\Donacion;
use App\Models\Donante;
use App\Models\Persona;
use App\Models\TipoMoneda;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class SeedUserWithDonations extends Command
{
    protected $signature = 'seed:user-donations';
    protected $description = 'Crea un usuario de prueba y le asigna 3 donaciones con certificados.';

    public function handle()
    {
        $this->info('🚀 Iniciando seeding de usuario y donaciones...');

        try {
            // 1. Crear o buscar usuario
            $email = 'donante.prueba@fundacion.org';
            $persona = Persona::firstOrCreate(
                ['correo_electronico' => $email],
                [
                    'nombre' => 'Donante',
                    'apellido_paterno' => 'Prueba',
                    'contrasenia' => Hash::make('password123'),
                    'activo' => 1
                ]
            );

            // 2. Crear perfil de donante
            $donante = Donante::firstOrCreate(
                ['persona_id' => $persona->id_persona],
                [
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido_paterno,
                    'email' => $persona->correo_electronico,
                    'telefono' => '77777777'
                ]
            );

            // 3. Asegurar moneda
            $moneda = TipoMoneda::firstOrCreate(
                ['codigo' => 'BOB'],
                ['descripcion' => 'Bolivianos', 'simbolo' => 'Bs.']
            );

            // 4. Crear 3 donaciones
            for ($i = 1; $i <= 3; $i++) {
                $donacion = Donacion::create([
                    'id_donante' => $donante->id_donante,
                    'monto' => 100.00 * $i,
                    'id_tipo_moneda' => $moneda->id_tipo_moneda,
                    'estado' => 'succeeded',
                    'proveedor' => 'seed_script',
                    'id_pago_proveedor' => 'seed_' . uniqid(),
                    'fecha' => now()->subDays($i),
                ]);

                GenerarCertificadoJob::dispatch($donacion);
                $this->line("✅ Donación ID: {$donacion->id_donacion} creada y Job despachado.");
            }

            $this->info("Usuario creado: {$email} / password123");
            $this->info('Comando finalizado.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
