<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $models = [
            'department',
            'user',
            'feature',
            'location',
            'building',
            'level',
            'room',
            'category',
            'sub_category',
            'asset',
            'asset_info',
        ];

        $permissions = collect($models)->flatMap(function ($model) {
            return ["$model-create", "$model-list", "$model-edit", "$model-delete"];
        })->merge([
            'department-assign-users',
            'feature-toggle',
            'role-create',
            'role-list',
            'role-edit',
            'category-list',
            'sub_category-list',
            'asset-list',
            'asset_info-list',
            'news-create',
            'news-list',
            'news-edit',
            'news-delete',
            'media-settings-manage',
        ])->toArray();

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'web']
            );
        }

        $superAdminRole = Role::updateOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);
        $superAdminRole->syncPermissions(Permission::where('guard_name', 'web')->get());

        $managerRole = Role::updateOrCreate(['name' => 'DepartmentManager', 'guard_name' => 'web']);
        $managerRole->syncPermissions([
            'asset-create',
            'asset-list',
            'asset-edit',
            'asset-delete',
            'asset_info-create',
            'asset_info-list',
            'asset_info-edit',
            'asset_info-delete',
            'location-list',
            'building-list',
            'level-list',
            'room-list',
            'category-list',
            'sub_category-list',
        ]);

        $staffRole = Role::updateOrCreate(['name' => 'DepartmentStaff', 'guard_name' => 'web']);
        $staffRole->syncPermissions([
            'asset-list',
            'asset_info-list',
            'location-list',
            'building-list',
            'level-list',
            'room-list',
            'category-list',
            'sub_category-list',
        ]);

        $mediaRole = Role::updateOrCreate(['name' => 'MediaManager', 'guard_name' => 'web']);
        $mediaRole->syncPermissions([
            'news-create',
            'news-list',
            'news-edit',
            'news-delete',
        ]);
    }
}
