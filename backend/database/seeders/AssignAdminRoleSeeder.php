<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class AssignAdminRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Find or create admin role
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['description' => 'Administrator']
        );
        
        // Find admin user
        $adminUser = User::where('email', 'admin@nuevo1.com')->first();
        
        if ($adminUser) {
            $adminUser->roles()->syncWithoutDetaching([$adminRole->id]);
            echo "Admin role assigned to {$adminUser->email}\n";
        } else {
            echo "Admin user not found\n";
        }
    }
}
