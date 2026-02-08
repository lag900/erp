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
        
        if ($user->hasRole('SuperAdmin')) {
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
            $departmentId = $departments->first()->id;
            $request->session()->put('selected_department_id', $departmentId);
            return redirect()->intended(route('dashboard'));
        }

        // إذا لم يكن لديه أي قسم، منعه من الدخول
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

        // التأكد أن المستخدم يملك صلاحية هذا القسم فعلياً (SuperAdmin يتخطى هذا الفحص)
        if (!$request->user()->hasRole('SuperAdmin')) {
            $belongsToDepartment = $request->user()
                ->departments()
                ->where('departments.id', $departmentId)
                ->exists();

            abort_unless($belongsToDepartment, 403, 'Unauthorized department access.');
        }

        $request->session()->put('selected_department_id', $departmentId);

        return redirect()->intended(route('dashboard'));
    }
}
