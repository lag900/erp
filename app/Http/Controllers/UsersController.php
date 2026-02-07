<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UsersController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        $users = User::with(['roles', 'departments'])
            ->when($departmentId && !$request->user()->hasRole('SuperAdmin'), fn ($query) => $query->whereHas('departments', fn ($q) => $q->where('departments.id', $departmentId)))
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

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'departments' => $departments,
        ]);
    }

    public function create(): Response
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'departments' => $departments,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'department_ids' => ['array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'default_department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $role = Role::findOrFail($data['role_id']);
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

        return redirect()->route('users.index');
    }

    public function edit(User $user): Response
    {
        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

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
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'integer', 'exists:roles,id'],
            'department_ids' => ['array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'default_department_id' => ['nullable', 'integer', 'exists:departments,id'],
        ]);

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password']
                ? Hash::make($data['password'])
                : $user->password,
        ]);

        $role = Role::findOrFail($data['role_id']);
        $user->syncRoles([$role->name]);

        $departmentIds = collect($data['department_ids'] ?? [])->unique()->values();
        $defaultDepartmentId = $data['default_department_id'] ?? null;

        if ($departmentIds->isEmpty()) {
            $user->departments()->detach();

            return redirect()->route('users.index');
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

        return redirect()->route('users.index');
    }

    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        return redirect()->route('users.index');
    }
}
