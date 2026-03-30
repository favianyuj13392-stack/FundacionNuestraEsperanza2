<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['nombre' => 'admin','descripcion' => 'Acceso total'],
            ['nombre' => 'editor','descripcion' => 'CMS y campañas'],
            ['nombre' => 'tesorero','descripcion' => 'Finanzas y donaciones'],
            ['nombre' => 'viewer','descripcion' => 'Solo lectura'],
        ];
        foreach ($roles as $r) {
            Rol::firstOrCreate(['nombre' => $r['nombre']], $r);
        }
    }
}
