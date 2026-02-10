<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentSelectionController extends Controller
{
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        
        // Extended Global Admin: SuperAdmin OR (Admin/Manager within ADMIN department)
        $isGlobalAdmin = $user->hasRole('SuperAdmin') || 
                        ($user->hasAnyRole(['Admin', 'Manager']) && $user->departments()->where('departments.code', 'ADMIN')->exists());

        if ($isGlobalAdmin) {
            $departments = \App\Models\Department::select('id', 'name')
                ->orderBy('name')
                ->get();
        } else {
            $departments = $user->departments()
                ->select('departments.id', 'departments.name')
                ->orderBy('departments.name')
                ->get();
        }

        // تجربة مستخدم محسنة: إذا كان لديه قسم واحد فقط، إعادته للوحة التحكم فوراً
        if ($departments->count() === 1) {
            $dept = $departments->first();
            $request->session()->put('selected_department_id', $dept->id);
            // Ensure strict global visibility for Mother Department staff
            $request->session()->put('is_admin_department', $dept->isAdmin() && $user->hasAnyRole(['SuperAdmin', 'Admin', 'Manager']));
            return redirect()->intended(route('dashboard'));
        }

        if ($departments->isEmpty()) {
            abort(403, 'No departments available in the system.');
        }

        return Inertia::render('Departments/Select', [
            'departments' => $departments,
            'selectedDepartmentId' => $request->session()->get('selected_department_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
        ]);

        $departmentId = (int) $data['department_id'];
        $department = \App\Models\Department::findOrFail($departmentId);

        // Extended Global Admin: SuperAdmin OR (Admin/Manager within ADMIN department)
        $isGlobalAdmin = $request->user()->hasRole('SuperAdmin') || 
                        ($request->user()->hasAnyRole(['Admin', 'Manager']) && $request->user()->departments()->where('departments.code', 'ADMIN')->exists());

        if (!$isGlobalAdmin) {
            $belongsToDepartment = $request->user()
                ->departments()
                ->where('departments.id', $departmentId)
                ->exists();

            abort_unless($belongsToDepartment, 403, 'Unauthorized department access.');
        }

        $request->session()->put('selected_department_id', $departmentId);
        // Ensure strict global visibility for Mother Department staff
        $request->session()->put('is_admin_department', $department->isAdmin() && $request->user()->hasAnyRole(['SuperAdmin', 'Admin', 'Manager']));

        return redirect()->intended(route('dashboard'));
    }
}
