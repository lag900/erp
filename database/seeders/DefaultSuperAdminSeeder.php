<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DefaultSuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * This seeder creates a temporary default super admin account
     * only if no users currently exist in the system.
     */
    public function run(): void
    {
        // Check if a SuperAdmin already exists (by role or by email 1@1.com)
        $superAdminExists = User::role('SuperAdmin')->exists() || User::where('email', '1@1.com')->exists();

        if (!$superAdminExists) {
            $user = User::create([
                'name' => 'System Owner',
                'email' => '1@1.com',
                'password' => Hash::make('1@1.com'),
                'email_verified_at' => now(),
                'role' => 'SuperAdmin', // Direct field fallback
                'is_active' => true,
            ]);

            // Ensure the SuperAdmin role exists and assign it
            $role = Role::where('name', 'SuperAdmin')->first();
            
            if ($role) {
                $user->assignRole($role);
            }
        }
    }
}
