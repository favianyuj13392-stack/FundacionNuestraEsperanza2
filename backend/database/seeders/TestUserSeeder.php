<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        $testUser = User::firstOrCreate(
            ['email' => 'panel.test@fundacion.org'],
            [
                'name' => 'Test',
                'last_name' => 'Usuario',
                'password' => Hash::make('password'),
                'is_active' => 1,
            ]
        );

        $roleViewerId = Role::where('name', 'viewer')->value('id');
        if ($roleViewerId) {
            $testUser->roles()->syncWithoutDetaching([$roleViewerId]);
        }
    }
}
