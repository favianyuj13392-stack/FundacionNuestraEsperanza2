<?php

namespace Database\Seeders;

use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Persona::firstOrCreate(
            ['correo_electronico' => 'admin@fundacion.org'],
            [
                'nombre' => 'Super',
                'apellido_paterno' => 'Admin',
                'contrasenia' => Hash::make('CambiaEsto123!'),
                'activo' => 1,
            ]
        );

        $rolAdminId = Rol::where('nombre','admin')->value('id_rol');
        if ($rolAdminId) {
            $admin->roles()->syncWithoutDetaching([$rolAdminId]);
        }
    }
}
