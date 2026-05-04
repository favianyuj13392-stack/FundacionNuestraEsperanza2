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
                'password' => Hash::make('CambiaEsto123!'),
                'is_active' => 1,
            ]
        );

        $roleAdminId = Role::where('name', 'admin')->value('id');
        if ($roleAdminId) {
            $admin->roles()->syncWithoutDetaching([$roleAdminId]);
        }
    }
}
