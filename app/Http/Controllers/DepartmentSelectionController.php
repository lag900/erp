<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentSelectionController extends Controller
{
    public function index(Request $request): Response
    {
        $departments = $request->user()
            ->departments()
            ->select('departments.id', 'departments.name')
            ->orderBy('departments.name')
            ->get();

        return Inertia::render('Departments/Select', [
            'departments' => $departments,
            'selectedDepartmentId' => $request->session()->get('selected_department_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_id' => ['required', 'integer'],
        ]);

        $departmentId = (int) $data['department_id'];

        $belongsToDepartment = $request->user()
            ->departments()
            ->where('departments.id', $departmentId)
            ->exists();

        abort_unless($belongsToDepartment, 403);

        $request->session()->put('selected_department_id', $departmentId);

        return redirect()->intended(route('dashboard'));
    }
}
