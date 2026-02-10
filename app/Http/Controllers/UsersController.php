<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();
        $isGlobalAdmin = session('is_admin_department');

        // 1. Hierarchy Logic: "If I have 20 roles (permissions), I see 1-19"
        // Calculate current user's weight based on their primary role's permission count
        $authUserRole = $authUser->roles->first();
        $myAuthWeight = $authUserRole ? $authUserRole->permissions()->count() : 0;

        // Get IDs of roles that are strictly lower in hierarchy
        $allowedRoleIds = Role::withCount('permissions')
            ->get()
            ->filter(fn ($role) => $role->permissions_count < $myAuthWeight)
            ->pluck('id');

        $departmentId = $request->session()->get('selected_department_id');
        
        // 2. Department Scope: If not Global Admin, restrict to own departments
        // 3. User Filter: Apply hierarchy and department constraints
        $users = User::with(['roles', 'departments'])
            ->when(!$isGlobalAdmin, function ($query) use ($authUser, $allowedRoleIds, $departmentId) {
                // Constraint A: Strict Role Hierarchy (Lower Level Only)
                $query->whereHas('roles', function ($q) use ($allowedRoleIds) {
                    $q->whereIn('id', $allowedRoleIds);
                });

                // Constraint B: Department Isolation
                // If a specific context is selected, use it. Otherwise, restrict to any of the user's departments.
                if ($departmentId) {
                    $query->whereHas('departments', fn ($q) => $q->where('departments.id', $departmentId));
                } else {
                    $myDeptIds = $authUser->departments->pluck('id');
                    $query->whereHas('departments', fn ($q) => $q->whereIn('departments.id', $myDeptIds));
                }
            })
            ->orderBy('name')
            ->get()
            ->map(function (User $user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->image,
                    'image_url' => $user->image_url,
                    'roles' => $user->roles->pluck('name')->values(),
                    'role_id' => $user->roles->first()?->id,
                    'departments' => $user->departments->pluck('name')->values(),
                    'department_ids' => $user->departments->pluck('id')->values(),
                    'default_department_id' => $user->departments->where('pivot.is_default', true)->first()?->id,
                ];
            });

        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = $isGlobalAdmin 
            ? Department::select('id', 'name')->orderBy('name')->get()
            : $authUser->departments()->select('departments.id', 'departments.name')->orderBy('departments.name')->get();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'departments' => $departments,
        ]);
    }

    public function create(Request $request): Response
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $authUser = $request->user();
        $isGlobalAdmin = session('is_admin_department');

        $departments = $isGlobalAdmin 
            ? Department::select('id', 'name')->orderBy('name')->get()
            : $authUser->departments()->select('departments.id', 'departments.name')->orderBy('departments.name')->get();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        \Illuminate\Support\Facades\Log::info('Raw email input: ' . json_encode($request->input('email')));

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'department_ids' => ['array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'default_department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        $roleName = Str::snake($role->name);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $roleName,
        ]);

        $user->assignRole($role);

        \Illuminate\Support\Facades\Log::info('User created: ' . $user->email . ' with role: ' . $role->name);

        $departmentIds = collect($data['department_ids'] ?? [])->unique()->values();
        $defaultDepartmentId = $data['default_department_id'] ?? null;

        if ($departmentIds->isNotEmpty()) {
            if ($departmentIds->count() === 1) {
                $defaultDepartmentId = $departmentIds->first();
            }

            $pivotData = $departmentIds->mapWithKeys(function ($departmentId) use ($defaultDepartmentId, $role) {
                return [
                    $departmentId => [
                        'is_default' => $defaultDepartmentId === $departmentId,
                        'role_id' => $role->id,
                    ],
                ];
            });

            $user->departments()->sync($pivotData->all());
            \Illuminate\Support\Facades\Log::info('Synced ' . $departmentIds->count() . ' departments for user: ' . $user->email);
        }

        \App\Traits\LogsActivity::log('user_created', "System access granted: Account created for {$user->name} with role {$role->name}", $user, [
            'role' => $role->name,
            'departments' => $departmentIds->all(),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(Request $request, User $user): Response
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $authUser = $request->user();
        $isGlobalAdmin = session('is_admin_department');

        $departments = $isGlobalAdmin 
            ? Department::select('id', 'name')->orderBy('name')->get()
            : $authUser->departments()->select('departments.id', 'departments.name')->orderBy('departments.name')->get();

        $user->load('roles', 'departments');

        $defaultDepartmentId = $user->departments()
            ->wherePivot('is_default', true)
            ->value('departments.id');

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role_id' => $user->roles->first()?->id,
                'department_ids' => $user->departments->pluck('id')->values(),
                'default_department_id' => $defaultDepartmentId,
            ],
            'roles' => $roles,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email:filter', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'department_ids' => ['array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'default_department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $role = Role::findOrFail($data['role_id']);
        $roleName = Str::snake($role->name);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
                ? Hash::make($data['password'])
                : $user->password,
            'role' => $roleName,
        ]);

        $user->syncRoles([$role->name]);

        $departmentIds = collect($data['department_ids'] ?? [])->unique()->values();
        $defaultDepartmentId = $data['default_department_id'] ?? null;

        if ($departmentIds->isEmpty()) {
            $user->departments()->detach();
            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        }

        if ($departmentIds->count() === 1) {
            $defaultDepartmentId = $departmentIds->first();
        }

        $pivotData = $departmentIds->mapWithKeys(function ($departmentId) use ($defaultDepartmentId, $role) {
            return [
                $departmentId => [
                    'is_default' => $defaultDepartmentId === $departmentId,
                    'role_id' => $role->id,
                ],
            ];
        });

        $user->departments()->sync($pivotData->all());

        \App\Traits\LogsActivity::log('user_updated', "Identity updated: Governance details modified for {$user->name}", $user, [
            'role' => $role->name,
            'department_ids' => $departmentIds->all(),
        ]);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user, Request $request): RedirectResponse
    {
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();

        // 1. Prevent self-deletion
        if ($authUser->id === $user->id) {
            return back()->with('error', 'Institutional Security: You cannot delete your own active session account.');
        }

        // 2. Ensure at least one SuperAdmin persists
        if ($user->hasRole('SuperAdmin')) {
            $superAdminCount = User::role('SuperAdmin')->count();
            if ($superAdminCount <= 1) {
                return back()->with('error', 'System Safeguard: Deleting the final Super Admin is prohibited to prevent terminal lockout.');
            }
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Account terminated successfully.');
    }
}
