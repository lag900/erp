<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionGroup extends Model
{
    protected $fillable = ['name', 'description', 'icon', 'sort_order'];

    /**
     * Get the permissions for this group.
     */
    public function permissions()
    {
        return $this->hasMany(\Spatie\Permission\Models\Permission::class);
    }
}
