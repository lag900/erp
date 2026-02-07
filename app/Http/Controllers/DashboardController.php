<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Building;
use App\Models\Category;
use App\Models\Department;
use App\Models\Level;
use App\Models\Location;
use App\Models\News;
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

        // Base query for department assets
        $assetsQuery = Asset::query();

        // 1. Asset Statistics
        $totalAssets = $departmentId ? $assetsQuery->sum('count') : 0;
        $totalValue = 0; // Placeholder if value field existed

        // 2. Assets by Category
        $assetsByCategory = [];
        if ($departmentId) {
            $assetsByCategory = Category::select('categories.name')
                ->join('sub_categories', 'categories.id', '=', 'sub_categories.category_id')
                ->join('assets', 'sub_categories.id', '=', 'assets.sub_category_id')
                ->selectRaw('categories.name, SUM(assets.count) as total')
                ->groupBy('categories.id', 'categories.name')
                ->get();
        }

        // 3. Recent Activity (Last 5 Updates/Additions)
        $recentActivity = [];
        if ($departmentId) {
            $recentActivity = Asset::with(['subCategory.category', 'room.level.building.location'])
                ->latest('updated_at')
                ->take(5)
                ->get()
                ->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'name' => $asset->subCategory?->name ?? 'Unknown Asset',
                        'category' => $asset->subCategory?->category?->name ?? 'Uncategorized',
                        'location' => $asset->room?->level?->building?->name . ' - ' . $asset->room?->name,
                        'updated_at' => $asset->updated_at->diffForHumans(),
                        'status' => 'Active', // Placeholder
                    ];
                });
        }

        // 4. Counts
        $stats = [
            'assets' => $totalAssets,
            'locations' => Location::count(),
            'buildings' => Building::count(),
            'rooms' => Room::count(),
            'users' => $departmentId
                ? \App\Models\User::whereHas('departments', fn ($q) => $q->where('departments.id', $departmentId))->count()
                : 0,
            'news' => News::count(),
        ];

        return Inertia::render('Dashboard', [
            'department' => $department,
            'stats' => $stats,
            'assetsByCategory' => $assetsByCategory,
            'recentActivity' => $recentActivity,
            'hasDepartmentSelected' => (bool) $departmentId,
        ]);
    }
}
