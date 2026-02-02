<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class DepartmentMembersController extends Controller
{
    public function index(Department $department): Response
    {
        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $roles = Role::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $memberships = $department->users()
            ->withPivot(['role_id', 'is_default'])
            ->get()
            ->mapWithKeys(function (User $user) {
                return [
                    $user->id => [
                        'role_id' => $user->pivot->role_id,
                        'is_default' => (bool) $user->pivot->is_default,
                    ],
                ];
            });

        return Inertia::render('Departments/Members', [
            'department' => $department->only('id', 'name', 'code'),
            'users' => $users,
            'roles' => $roles,
            'memberships' => $memberships,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'members' => ['array'],
            'members.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'members.*.role_id' => ['required', 'integer', 'exists:roles,id'],
            'members.*.is_default' => ['boolean'],
        ]);

        $members = collect($data['members'] ?? []);

        $payload = $members->mapWithKeys(function ($member) {
            return [
                $member['user_id'] => [
                    'role_id' => $member['role_id'],
                    'is_default' => (bool) ($member['is_default'] ?? false),
                ],
            ];
        });

        DB::transaction(function () use ($department, $payload, $members) {
            $department->users()->sync($payload->all());

            $defaultUserIds = $members
                ->filter(fn ($member) => !empty($member['is_default']))
                ->pluck('user_id')
                ->values();

            if ($defaultUserIds->isNotEmpty()) {
                DB::table('department_user')
                    ->whereIn('user_id', $defaultUserIds)
                    ->where('department_id', '!=', $department->id)
                    ->update(['is_default' => false]);
            }
        });

        return redirect()->route('departments.members', $department);
    }
}
