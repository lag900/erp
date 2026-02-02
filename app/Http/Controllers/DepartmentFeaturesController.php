<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\Feature;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentFeaturesController extends Controller
{
    public function index(): Response
    {
        $departments = Department::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $features = Feature::query()
            ->orderBy('name')
            ->get(['id', 'name', 'key']);

        $departmentFeatures = Department::with(['features'])
            ->get()
            ->mapWithKeys(function (Department $department) {
                $featureMap = $department->features
                    ->mapWithKeys(function ($feature) {
                        return [
                            $feature->id => (bool) ($feature->pivot->is_enabled ?? false),
                        ];
                    })
                    ->all();

                return [
                    $department->id => $featureMap,
                ];
            });

        return Inertia::render('Departments/Features', [
            'departments' => $departments,
            'features' => $features,
            'departmentFeatures' => $departmentFeatures,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'features' => ['required', 'array'],
            'features.*.id' => ['required', 'integer', 'exists:features,id'],
            'features.*.is_enabled' => ['required', 'boolean'],
        ]);

        $payload = collect($data['features'])->mapWithKeys(function ($item) {
            return [
                $item['id'] => [
                    'is_enabled' => $item['is_enabled'],
                ],
            ];
        });

        $department->features()->sync($payload->all());

        return redirect()->route('departments.features');
    }
}
