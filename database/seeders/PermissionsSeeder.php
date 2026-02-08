<?php

namespace Database\Seeders;

use App\Constants\PermissionConstants;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // 1. Create all defined permission groups and permissions
        foreach (PermissionConstants::GROUPS as $groupName => $permissions) {
            $group = \App\Models\PermissionGroup::firstOrCreate(
                ['name' => $groupName],
                [
                    'description' => "Manage everything related to {$groupName}",
                    'icon' => $this->getIconForGroup($groupName)
                ]
            );

            foreach ($permissions as $permission) {
                $data = ['permission_group_id' => $group->id];

                if (isset(PermissionConstants::SIDEBAR_MAP[$permission])) {
                    $map = PermissionConstants::SIDEBAR_MAP[$permission];
                    $data['is_sidebar_item'] = true;
                    $data['sidebar_label'] = $map['label'];
                    $data['route_name'] = $map['route'];
                    $data['icon'] = $map['icon'];
                }

                Permission::updateOrCreate(
                    ['name' => $permission, 'guard_name' => 'web'],
                    $data
                );
            }
        }

        // 2. Define Roles and Assign Relevant Permissions

        // --- Role: Admin (The Department Master / System Admin) ---
        $adminRole = Role::updateOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        $adminRole->syncPermissions(PermissionConstants::all());

        // --- Role: Manager (Operational Control) ---
        $managerRole = Role::updateOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([
            // Assets
            'asset-list', 'asset-create', 'asset-view', 'asset-edit', 'asset-transfer',
            // Categories
            'category-list', 'category-create', 'category-edit', 'sub_category-list', 'sub_category-create', 
            // Locations
            'location-list', 'building-list', 'level-list', 'room-list',
            // News
            'news-list', 'news-create', 'news-edit',
            // Reports
            'report-access', 'report-export',
            // Branding
            'branding-manage',
        ]);

        // --- Role: Data Entry (Basic Input) ---
        $dataEntryRole = Role::updateOrCreate(['name' => 'Data Entry', 'guard_name' => 'web']);
        $dataEntryRole->syncPermissions([
            'asset-list', 'asset-create', 'asset-view',
            'category-list', 'sub_category-list',
            'location-list', 'building-list', 'level-list', 'room-list',
            'news-list', 'news-create',
        ]);

        // Keep SuperAdmin for internal support / total control bypass logic
        $superAdminRole = Role::updateOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(PermissionConstants::all());
    }

    private function getIconForGroup($group)
    {
        return match ($group) {
            'Assets' => 'cube',
            'Categories' => 'tag',
            'Locations' => 'map-pin',
            'Departments' => 'office-building',
            'Users & Roles' => 'user-group',
            'Media & News' => 'newspaper',
            'Reports' => 'chart-bar',
            'Administration' => 'library',
            default => 'shield-check',
        };
    }
}
