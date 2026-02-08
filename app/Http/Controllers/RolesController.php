<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesController extends Controller
{
    public function index(): Response
    {
        $roles = Role::withCount('permissions')
            ->orderBy('name')
            ->get()
            ->map(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'permissions_count' => $role->permissions_count,
                ];
            });

        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        $groups = \App\Models\PermissionGroup::with(['permissions' => function($q) {
            $q->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name')->get();

        $ungrouped = Permission::whereNull('permission_group_id')->orderBy('name')->get();

        return Inertia::render('Roles/Create', [
            'groups' => $groups,
            'ungrouped' => $ungrouped,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::create([
            'name' => $data['name'],
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($data['permission_ids'] ?? []);
        
        \App\Traits\LogsActivity::log('role_created', "Created security role: {$role->name}", $role);

        return redirect()->route('roles.index')->with('success', 'Identity role established.');
    }

    public function edit(Role $role): Response
    {
        $groups = \App\Models\PermissionGroup::with(['permissions' => function($q) {
            $q->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name')->get();

        $ungrouped = Permission::whereNull('permission_group_id')->orderBy('name')->get();

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'permission_ids' => $role->permissions()->pluck('id')->values(),
            ],
            'groups' => $groups,
            'ungrouped' => $ungrouped,
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
            'permission_ids' => ['array'],
            'permission_ids.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role->update([
            'name' => $data['name'],
        ]);

        $permissions = Permission::whereIn('id', $data['permission_ids'] ?? [])->get();
        $role->syncPermissions($permissions);

        \App\Traits\LogsActivity::log('role_updated', "Updated security role: {$role->name}", $role);

        return redirect()->route('roles.index');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->name === 'SuperAdmin') {
            abort(403, 'SuperAdmin role cannot be deleted.');
        }

        $role->delete();

        \App\Traits\LogsActivity::log('role_deleted', "Deleted security role: {$role->name}");

        return redirect()->route('roles.index');
    }
}
