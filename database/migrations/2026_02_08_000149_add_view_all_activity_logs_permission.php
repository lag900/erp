<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'view_all_activity_logs', 'guard_name' => 'web']);
        
        $superAdmin = Role::where('name', 'SuperAdmin')->first();
        if ($superAdmin instanceof Role) {
            $superAdmin->givePermissionTo($permission);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $permission = Permission::where('name', 'view_all_activity_logs')->first();
        if ($permission instanceof Permission) {
            $permission->delete();
        }
    }
};
