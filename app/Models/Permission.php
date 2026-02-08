<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
        'permission_group_id',
        'description',
        'status',
        'sidebar_label',
        'route_name',
        'icon',
        'is_sidebar_item',
        'sort_order',
    ];

    public function group()
    {
        return $this->belongsTo(PermissionGroup::class, 'permission_group_id');
    }
}
