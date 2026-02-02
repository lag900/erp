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
        $permissions = [];

        if ($request->user()) {
            $permissions = $request->user()
                ->getAllPermissions()
                ->pluck('name')
                ->values();

            $featureKeys = [];
            $selectedDepartmentId = $request->session()->get('selected_department_id');

            if ($selectedDepartmentId) {
                $department = Department::with('features')->find($selectedDepartmentId);

                if ($department) {
                    $featureKeys = $department->features
                        ->filter(fn ($feature) => (bool) ($feature->pivot->is_enabled ?? false))
                        ->pluck('key')
                        ->values();
                }
            }

            $departmentPayload = [
                'selectedId' => $request->session()->get('selected_department_id'),
                'list' => $request->user()
                    ->departments()
                    ->select('departments.id', 'departments.name')
                    ->orderBy('departments.name')
                    ->get(),
                'featuresEnabled' => $featureKeys,
            ];
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
                'permissions' => $permissions,
            ],
            'department' => $departmentPayload,
        ];
    }
}
