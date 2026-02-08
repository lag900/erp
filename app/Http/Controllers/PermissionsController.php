<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Models\Permission;

use App\Models\PermissionGroup;

class PermissionsController extends Controller
{
    public function index(): Response
    {
        $groups = PermissionGroup::with(['permissions' => function ($query) {
            $query->orderBy('name');
        }])->orderBy('sort_order')->orderBy('name')->get();

        // Handle permissions without a group
        $ungrouped = Permission::whereNull('permission_group_id')
            ->orderBy('name')
            ->get();

        $roles = \Spatie\Permission\Models\Role::withCount('permissions')->orderBy('name')->get();

        return Inertia::render('Permissions/Index', [
            'groups' => $groups,
            'ungrouped' => $ungrouped,
            'roles' => $roles,
        ]);
    }

    /**
     * Bulk assign permissions to roles
     */
    public function bulkAssign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
            'role_ids' => ['required', 'array'],
            'role_ids.*' => ['exists:roles,id'],
        ]);

        $permissions = Permission::whereIn('id', $data['permission_ids'])->get();
        /** @var \Spatie\Permission\Models\Role[] $roles */
        $roles = \Spatie\Permission\Models\Role::whereIn('id', $data['role_ids'])->get();

        foreach ($roles as $role) {
            $role->syncPermissions(array_merge($role->permissions->pluck('name')->toArray(), $permissions->pluck('name')->toArray()));
        }

        \App\Traits\LogsActivity::log('permission_bulk_assign', "Assigned " . count($data['permission_ids']) . " gates to " . count($roles) . " identities.");

        return redirect()->back()->with('success', count($data['permission_ids']) . ' gates mapped to selected identities.');
    }

    /**
     * Bulk remove permissions from roles
     */
    public function bulkRemove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permission_ids' => ['required', 'array'],
            'role_ids' => ['required', 'array'],
        ]);

        $permissions = Permission::whereIn('id', $data['permission_ids'])->get();
        /** @var \Spatie\Permission\Models\Role[] $roles */
        $roles = \Spatie\Permission\Models\Role::whereIn('id', $data['role_ids'])->get();

        foreach ($roles as $role) {
            $role->revokePermissionTo($permissions);
        }

        \App\Traits\LogsActivity::log('permission_bulk_revoke', "Revoked " . count($data['permission_ids']) . " gates from " . count($roles) . " identities.");

        return redirect()->back()->with('success', 'Security gates detached from identities.');
    }

    public function create(): Response
    {
        return Inertia::render('Permissions/Create', [
            'groups' => PermissionGroup::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permissions,name'],
            'permission_group_id' => ['required', 'exists:permission_groups,id'],
            'description' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
            'is_sidebar_item' => ['required', 'boolean'],
            'sidebar_label' => ['required_if:is_sidebar_item,true', 'nullable', 'string', 'max:255'],
            'route_name' => ['required_if:is_sidebar_item,true', 'nullable', 'string', 'max:255'],
            'icon' => ['required_if:is_sidebar_item,true', 'nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $permission = Permission::create([
            'name' => $data['name'],
            'permission_group_id' => $data['permission_group_id'],
            'description' => $data['description'],
            'status' => $data['status'],
            'is_sidebar_item' => $data['is_sidebar_item'],
            'sidebar_label' => $data['sidebar_label'] ?? null,
            'route_name' => $data['route_name'] ?? null,
            'icon' => $data['icon'] ?? 'cube',
            'sort_order' => $data['sort_order'] ?? 0,
            'guard_name' => 'web',
        ]);

        \App\Traits\LogsActivity::log('permission_created', "Created security gate: {$permission->name}", $permission);

        return redirect()->route('permissions.index')->with('success', 'Gate added to security matrix.');
    }

    /**
     * Move a permission to a different group (Drag and Drop)
     */
    public function move(Request $request, Permission $permission): RedirectResponse
    {
        $data = $request->validate([
            'permission_group_id' => ['required', 'exists:permission_groups,id'],
        ]);

        $permission->update([
            'permission_group_id' => $data['permission_group_id'],
        ]);

        \App\Traits\LogsActivity::log('permission_moved', "Relocated security gate: {$permission->name}", $permission);

        return redirect()->back()->with('success', 'Security gate relocated.');
    }

    /**
     * Bulk move permissions to a different group
     */
    public function bulkMove(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'permission_ids' => ['required', 'array'],
            'permission_ids.*' => ['exists:permissions,id'],
            'permission_group_id' => ['required', 'exists:permission_groups,id'],
        ]);

        Permission::whereIn('id', $data['permission_ids'])->update([
            'permission_group_id' => $data['permission_group_id'],
        ]);

        return redirect()->back()->with('success', count($data['permission_ids']) . ' gates relocated to target module.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        if ($permission->name === 'feature-toggle') {
            abort(403, 'Global override gate cannot be purged.');
        }

        $name = $permission->name;
        $permission->delete();

        \App\Traits\LogsActivity::log('permission_deleted', "Revoked security gate: {$name}");

        return redirect()->route('permissions.index')->with('success', 'Gate revoked.');
    }
}
