<?php

namespace Database\Seeders;

use App\Models\Persona;
use App\Models\Rol;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = Persona::firstOrCreate(
            ['correo_electronico' => 'panel.test@fundacion.org'],
            [
                'nombre' => 'Test',
                'apellido_paterno' => 'Usuario',
                'contrasenia' => Hash::make('password'),
                'activo' => 1,
            ]
        );

        $rolViewerId = Rol::where('nombre', 'viewer')->value('id_rol');
        if ($rolViewerId) {
            $testUser->roles()->syncWithoutDetaching([$rolViewerId]);
        }
    }
}
