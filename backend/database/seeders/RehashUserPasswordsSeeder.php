<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RehashUserPasswordsSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users with non-bcrypt passwords
        $users = User::all();
        
        foreach ($users as $user) {
            // Check if password is already bcrypt (starts with $2y$)
            if (!str_starts_with($user->password, '$2y$')) {
                echo "Rehashing password for user: {$user->email}\n";
                // Assume the old password was the same as email prefix for demo
                // In production, you'd need to know the actual passwords
                $user->password = Hash::make('password123');
                $user->save();
            }
        }
        
        echo "Password rehashing complete!\n";
    }
}
