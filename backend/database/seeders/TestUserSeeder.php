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

        $rolViewer = Role::where('name', 'viewer')->first();
        if ($rolViewer) {
            $testUser->roles()->syncWithoutDetaching([$rolViewer->id]);
        }
    }
}