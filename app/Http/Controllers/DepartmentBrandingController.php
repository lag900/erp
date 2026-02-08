<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentBrandingController extends Controller
{
    public function edit(Department $department): Response
    {
        return Inertia::render('Departments/Branding', [
            'department' => [
                'id' => $department->id,
                'name' => $department->name,
                'arabic_name' => $department->arabic_name,
                'university_logo_url' => $department->university_logo_url,
                'department_logo_url' => $department->department_logo_url,
            ],
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $request->validate([
            'arabic_name' => ['nullable', 'string', 'max:255'],
            'university_logo' => ['nullable', 'image', 'max:2048'],
            'department_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = [
            'arabic_name' => $request->arabic_name,
        ];

        if ($request->hasFile('university_logo')) {
            // Delete old one if exists
            if ($department->university_logo) {
                Storage::disk('public')->delete($department->university_logo);
            }
            $data['university_logo'] = $request->file('university_logo')->store('branding', 'public');
        }

        if ($request->hasFile('department_logo')) {
            // Delete old one if exists
            if ($department->department_logo) {
                Storage::disk('public')->delete($department->department_logo);
            }
            $data['department_logo'] = $request->file('department_logo')->store('branding', 'public');
        }

        $department->update($data);

        \App\Traits\LogsActivity::log('branding_updated', "Updated branding for department: {$department->name}", $department);

        return redirect()->back()->with('success', 'Department branding updated successfully.');
    }
}
