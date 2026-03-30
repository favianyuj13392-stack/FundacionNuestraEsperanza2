<?php

namespace Database\Seeders;

use App\Models\Role; 
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            // Cambiamos 'nombre' por 'name' y 'descripcion' por 'description'
            ['name' => 'admin', 'description' => 'Acceso total'],
            ['name' => 'editor', 'description' => 'CMS y campañas'],
            ['name' => 'tesorero', 'description' => 'Finanzas y donaciones'],
            ['name' => 'viewer', 'description' => 'Solo lectura'],
        ];
        foreach ($roles as $r) {
            Role::firstOrCreate(['name' => $r['name']], $r);
        }
    }
}