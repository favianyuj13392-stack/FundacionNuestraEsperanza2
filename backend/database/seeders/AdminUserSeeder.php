<?php

namespace Database\Seeders;

use App\Models\User; 
use App\Models\Role; 
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['email' => 'admin@fundacion.org'],
            [
                'name' => 'Super',
                'last_name' => 'Admin',
                'password' => Hash::make('conAdmin123'),
                'is_active' => 1,
            ]
        );

        // Buscamos el rol y lo asignamos
        $rolAdmin = Role::where('name', 'admin')->first();
        if ($rolAdmin) {
            // Asumiendo que la relación en tu modelo User se llama roles()
            $admin->roles()->syncWithoutDetaching([$rolAdmin->id]);
        }
    }
}