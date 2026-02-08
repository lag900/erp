<?php

namespace App\Constants;

class PermissionConstants
{
    const GROUPS = [
        'Assets' => [
            'asset-list',
            'asset-create',
            'asset-view',
            'asset-edit',
            'asset-delete',
            'asset-transfer',
        ],
        'Categories' => [
            'category-list',
            'category-create',
            'category-edit',
            'category-delete',
            'sub_category-list',
            'sub_category-create',
            'sub_category-edit',
            'sub_category-delete',
        ],
        'Locations' => [
            'location-list',
            'location-create',
            'location-edit',
            'location-delete',
            'building-list',
            'building-create',
            'building-edit',
            'building-delete',
            'level-list',
            'level-create',
            'level-edit',
            'level-delete',
            'room-list',
            'room-create',
            'room-edit',
            'room-delete',
        ],
        'Departments' => [
            'department-list',
            'department-create',
            'department-edit',
            'department-delete',
            'department-assign-users',
            'feature-toggle',
            'branding-manage',
        ],
        'Users & Roles' => [
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'permission-list',
            'permission-create',
            'permission-delete',
            'view_all_activity_logs',
        ],
        'Administration' => [
            'administration-list',
            'administration-create',
            'administration-edit',
            'administration-delete',
        ],
        'Media & News' => [
            'news-list',
            'news-create',
            'news-edit',
            'news-delete',
            'media-settings-manage',
        ],
        'Reports' => [
            'report-access',
            'report-export',
        ],
    ];

    const SIDEBAR_MAP = [
        'asset-list' => [
            'label' => 'Assets',
            'route' => 'assets.index',
            'icon' => 'cube',
        ],
        'location-list' => [
            'label' => 'Locations',
            'route' => 'locations.index',
            'icon' => 'map-pin',
        ],
        'building-list' => [
            'label' => 'Buildings',
            'route' => 'buildings.index',
            'icon' => 'office-building',
        ],
        'level-list' => [
            'label' => 'Levels',
            'route' => 'levels.index',
            'icon' => 'menu-alt-2',
        ],
        'room-list' => [
            'label' => 'Rooms',
            'route' => 'rooms.index',
            'icon' => 'cube-transparent',
        ],
        'category-list' => [
            'label' => 'Categories',
            'route' => 'categories.index',
            'icon' => 'tag',
        ],
        'sub_category-list' => [
            'label' => 'Subcategories',
            'route' => 'subcategories.index',
            'icon' => 'collection',
        ],
        'department-list' => [
            'label' => 'Departments',
            'route' => 'departments.index',
            'icon' => 'briefcase',
        ],
        'feature-toggle' => [
            'label' => 'Features',
            'route' => 'departments.features',
            'icon' => 'adjustments',
        ],
        'user-list' => [
            'label' => 'Users',
            'route' => 'users.index',
            'icon' => 'users',
        ],
        'role-list' => [
            'label' => 'Roles',
            'route' => 'roles.index',
            'icon' => 'shield-check',
        ],
        'permission-list' => [
            'label' => 'Permissions',
            'route' => 'permissions.index',
            'icon' => 'lock-closed',
        ],
        'administration-list' => [
            'label' => 'Organization',
            'route' => 'administration.index',
            'icon' => 'library',
        ],
        'news-list' => [
            'label' => 'News & Events',
            'route' => 'media.news.index',
            'icon' => 'newspaper',
        ],
        'media-settings-manage' => [
            'label' => 'Media Settings',
            'route' => 'media.settings.edit',
            'icon' => 'cog',
        ],
        'report-access' => [
            'label' => 'Reports',
            'route' => 'reports.index',
            'icon' => 'chart-bar',
        ],
    ];

    public static function all(): array
    {
        return collect(self::GROUPS)->flatten()->toArray();
    }

    public static function getFlatGrouped(): array
    {
        $flat = [];
        foreach (self::GROUPS as $group => $permissions) {
            foreach ($permissions as $permission) {
                $flat[$permission] = $group;
            }
        }
        return $flat;
    }
}
