<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Building;
use App\Models\Category;
use App\Models\Department;
use App\Models\Level;
use App\Models\Location;
use App\Models\Room;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        $department = $departmentId ? Department::find($departmentId) : null;

        $stats = [
            'assets' => $departmentId
                ? Asset::where('department_id', $departmentId)->count()
                : Asset::count(),
            'locations' => Location::count(),
            'buildings' => Building::count(),
            'levels' => Level::count(),
            'rooms' => Room::count(),
            'categories' => Category::count(),
            'subCategories' => SubCategory::count(),
            'departments' => Department::count(),
            'users' => $request->user()->can('user-list') 
                ? \App\Models\User::count() 
                : 0,
        ];

        return Inertia::render('Dashboard', [
            'department' => $department,
            'stats' => $stats,
            'hasDepartmentSelected' => (bool) $departmentId,
        ]);
    }
}
