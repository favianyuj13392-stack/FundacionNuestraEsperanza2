<?php

namespace App\Console\Commands;

use App\Jobs\GenerarCertificadoJob;
use App\Models\Donacion;
use App\Models\Donante;
use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class TestUsuarioDonanteCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:usuario-donante';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un usuario con rol "donante", lo vincula a un Donante y simula una donación con certificado.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando simulación de usuario y certificado complejo...');

        try {
            // --- 1. Crear/Verificar el Rol 'donante' ---
            $this->line('1. Creando/Verificando rol "donante"...');
            $rolDonante = Rol::firstOrCreate(
                ['nombre' => 'donante'],
                ['descripcion' => 'Usuario con capacidad de ver historial de donaciones y descargar certificados.']
            );
            $this->info("   -> Rol [{$rolDonante->nombre}] listo.");

            // --- 2. Crear/Verificar la Persona ---
            $email = 'panel.test@fundacion.org';
            $password = 'password';
            $this->line("2. Creando/Verificando Persona: {$email}");

            $persona = Persona::firstOrCreate(
                ['correo_electronico' => $email],
                [
                    'nombre' => 'Usuario',
                    'apellido_paterno' => 'Panel',
                    'apellido_materno' => 'Test',
                    'contrasenia' => Hash::make($password), // Usar Hash para la contraseña
                    'ci' => '12345678TEST',
                    'activo' => true,
                ]
            );

            // --- 3. Asignar el Rol a la Persona ---
            if (!$persona->hasRole('donante')) {
                $persona->roles()->attach($rolDonante->id_rol, ['asignado_en' => now()]);
                $this->info('   -> Rol "donante" asignado a la Persona.');
            } else {
                $this->line('   -> Rol "donante" ya estaba asignado.');
            }

            // --- 4. Crear/Verificar el registro de Donante (vinculado a la Persona) ---
            // La tabla 'donantes' tiene una columna 'persona_id' que apunta a 'id_persona'.
            $this->line('3. Creando/Verificando registro de Donante...');
            $donante = Donante::firstOrCreate(
                ['persona_id' => $persona->id_persona],
                [
                    'nombre' => $persona->nombre,
                    'apellido' => $persona->apellido_paterno,
                    'email' => $persona->correo_electronico,
                    'telefono' => '777-DONA-TEST',
                ]
            );
            $this->info("   -> Donante [ID: {$donante->id_donante}] vinculado a Persona [ID: {$persona->id_persona}].");


            // --- 5. Simular la creación de una Donación ---
            $this->line('4. Creando donación de prueba...');
            $donacion = Donacion::create([
                'id_donante' => $donante->id_donante,
                'id_campania' => null, 
                'id_tipo_moneda' => 1, // Asumimos ID 1 para la moneda de prueba
                'monto' => 300.00, // Monto que califica para certificado
                'estado' => 'succeeded',
                'proveedor' => 'test_auth',
                'id_pago_proveedor' => 'auth_test_' . uniqid(),
                'fecha' => now(),
            ]);
            $this->info("   -> Donación [ID: {$donacion->id_donacion}] creada.");


            // --- 6. Despachar el Job para generar el certificado ---
            $this->info('5. Despachando job a la cola para generar el certificado...');
            GenerarCertificadoJob::dispatch($donacion);

            $this->line('--------------------------------------------------');
            $this->info('✅ ¡Simulación Completa!');
            $this->info("  - Usuario: {$email}");
            $this->info("  - Password: {$password}");
            $this->info("  - Certificado en camino (revisa queue:work)");
            $this->line('--------------------------------------------------');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error durante la simulación:');
            $this->error($e->getMessage());
            Log::error('Error en TestUsuarioDonanteCommand: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}