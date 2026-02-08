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
        // Only run if no users exist in the system
        if (User::count() === 0) {
            $user = User::create([
                'name' => 'Default Super Admin',
                'email' => '1@1.com',
                'password' => Hash::make('123456'),
                'email_verified_at' => now(),
                'role' => 'super_admin',
                'is_active' => true,
            ]);

            // Ensure the SuperAdmin role exists and has all permissions
            // Note: This assumes PermissionsSeeder has already run.
            $role = Role::where('name', 'SuperAdmin')->first();
            
            if ($role) {
                $user->assignRole($role);
            }
        }
    }
}
