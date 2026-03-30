<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SeedAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seed:admin-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Crea un usuario administrador para acceso al panel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = 'admin@fundacion.org';
        $password = 'admin123';

        $this->info("Creando usuario administrador...");

        // Verificar si ya existe
        $user = User::where('correo_electronico', $email)->first();

        if ($user) {
            $this->warn("El usuario {$email} ya existe. Actualizando credenciales y estado...");
            $user->contrasenia = Hash::make($password);
            $user->activo = 1;
            $user->save();
        } else {
            $user = User::create([
                'nombre' => 'Administrador',
                'apellido_paterno' => 'Sistema',
                'ci' => '0000000',
                'correo_electronico' => $email,
                'contrasenia' => Hash::make($password),
                'activo' => 1,
            ]);
            $this->info("Usuario creado exitosamente.");
        }

        $this->newLine();
        $this->info("---------------------------------------");
        $this->info("CREDENCIALES DE ADMINISTRADOR:");
        $this->info("Email:    {$email}");
        $this->info("Password: {$password}");
        $this->info("---------------------------------------");

        return Command::SUCCESS;
    }
}
