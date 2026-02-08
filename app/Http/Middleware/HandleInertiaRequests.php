<?php

namespace App\Http\Middleware;

use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $departmentPayload = null;
        $permissions = collect([]);

        if ($user = $request->user()) {
            /** @var \App\Models\User $user */
            
            // Get all permissions for checking 'can' in frontend
            if ($user->hasRole('SuperAdmin')) {
                $permissions = \Spatie\Permission\Models\Permission::pluck('name')->values();
            } else {
                $permissions = $user->getAllPermissions()->pluck('name')->values();
            }

            $selectedDepartmentId = $request->session()->get('selected_department_id');
            $featureKeys = collect([]);
            $department = null;

            if ($selectedDepartmentId) {
                $department = Department::with('features')->find($selectedDepartmentId);
                if ($department) {
                    $featureKeys = $department->features
                        ->filter(fn ($feature) => (bool) ($feature->pivot->is_enabled ?? false))
                        ->pluck('key')
                        ->values();
                }
            }

            // Build dynamic sidebar
            $sidebar = \App\Models\PermissionGroup::with(['permissions' => function ($query) use ($permissions) {
                    $query->where('is_sidebar_item', true)
                          ->whereIn('name', $permissions)
                          ->orderBy('sort_order');
                }])
                ->orderBy('sort_order')
                ->get()
                ->map(function ($group) use ($featureKeys) {
                    // Specific logic for Assets group - hide if asset feature not enabled
                    if ($group->name === 'Assets' && !in_array('assets', $featureKeys->toArray())) {
                        return null;
                    }

                    // Specific logic for Reports group - hide if reports feature not enabled
                    if ($group->name === 'Reports' && !in_array('reports', $featureKeys->toArray())) {
                        return null;
                    }

                    $items = $group->permissions->map(function ($permission) {
                        return [
                            'label' => $permission->sidebar_label,
                            'route' => $permission->route_name,
                            'icon' => $permission->icon,
                            'permission' => $permission->name,
                        ];
                    });

                    if ($items->isEmpty()) {
                        return null;
                    }

                    return [
                        'group' => $group->name,
                        'items' => $items,
                    ];
                })
                ->filter()
                ->values();

            $departmentPayload = [
                'selectedId' => $selectedDepartmentId,
                'list' => $user->departments()
                    ->select('departments.id', 'departments.name')
                    ->orderBy('departments.name')
                    ->get(),
                'featuresEnabled' => $featureKeys,
                'sidebar' => $sidebar,
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $permissions,
                'roles' => $request->user() ? $request->user()->getRoleNames() : [],
            ],
            'departmentContext' => $departmentPayload,
        ];
    }
}
