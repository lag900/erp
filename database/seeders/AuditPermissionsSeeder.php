<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuditPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'view-audit-logs',
            'manage-audit-logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign to SuperAdmin role
        try {
            $superAdmin = Role::findByName('SuperAdmin', 'web');
            $superAdmin->givePermissionTo($permissions);
        } catch (\Exception $e) {
            // Role might not exist in this environment yet
        }
    }
}
