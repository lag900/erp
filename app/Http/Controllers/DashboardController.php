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
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');
        $department = $departmentId ? Department::find($departmentId) : null;
        $user = $request->user();

        return Inertia::render('Dashboard', [
            'department' => $department,
            'stats' => $this->getStats($departmentId),
            'assetsByCategory' => $this->getAssetsByCategory($departmentId),
            'assetsBySubcategory' => $this->getAssetsBySubcategory($departmentId),
            'topAssetGroups' => $this->getTopAssetGroups($departmentId),
            'mostEnteredData' => $this->getMostEnteredData($departmentId),
            'topContributors' => $this->getTopContributors($departmentId),
            'activityLogs' => $this->getActivityLogs($request, $user, $departmentId),
            'logActions' => AuditLog::distinct()->pluck('action_type'),
            'allUsers' => ($user->hasRole('SuperAdmin') || $user->can('view-audit-logs'))
                ? User::orderBy('name')->get(['id', 'name'])
                : [],
            'recentActivity' => $this->getRecentActivity($departmentId),
            'hasDepartmentSelected' => (bool) $departmentId,
            'hasMultipleDepartments' => $user->departments()->count() > 1,
        ]);
    }

    private function getStats($departmentId): array
    {
        return [
            'assets' => $departmentId ? Asset::where('department_id', $departmentId)->sum('count') : 0,
            'locations' => Location::count(),
            'buildings' => Building::count(),
            'rooms' => Room::count(),
            'users' => $departmentId
                ? User::whereHas('departments', fn ($q) => $q->where('departments.id', $departmentId))->count()
                : 0,
            'news' => News::count(),
        ];
    }

    private function getAssetsByCategory($departmentId)
    {
        if (!$departmentId) return [];

        return Category::select('categories.name', DB::raw('SUM(assets.count) as total'))
            ->join('assets', 'categories.id', '=', 'assets.category_id')
            ->where('assets.department_id', $departmentId)
            ->groupBy('categories.id', 'categories.name')
            ->get();
    }

    private function getRecentActivity($departmentId)
    {
        if (!$departmentId) return [];

        return Asset::with(['category', 'subCategory', 'room.level.building.location'])
            ->where('department_id', $departmentId)
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn($asset) => [
                'id' => $asset->id,
                'name' => ($asset->category?->name ?? 'Unknown') . ($asset->subCategory ? " - {$asset->subCategory->name}" : ""),
                'category' => $asset->category?->name ?? 'Uncategorized',
                'location' => $asset->room?->level?->building?->name . ' - ' . $asset->room?->name,
                'updated_at' => $asset->updated_at->diffForHumans(),
                'status' => 'Active', 
            ]);
    }

    private function getAssetsBySubcategory($departmentId)
    {
        if (!$departmentId) return [];

        return SubCategory::select('sub_categories.name', DB::raw('SUM(assets.count) as total'))
            ->join('assets', 'sub_categories.id', '=', 'assets.sub_category_id')
            ->where('assets.department_id', $departmentId)
            ->groupBy('sub_categories.id', 'sub_categories.name')
            ->orderByDesc('total')
            ->get();
    }

    private function getTopAssetGroups($departmentId)
    {
        if (!$departmentId) return [];

        return Asset::select('group_name', DB::raw('SUM(count) as total'))
            ->whereNotNull('group_name')
            ->where('department_id', $departmentId)
            ->groupBy('group_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }

    private function getMostEnteredData($departmentId)
    {
        if (!$departmentId) return [];

        return SubCategory::select('sub_categories.name', DB::raw('SUM(assets.count) as total'))
            ->join('assets', 'sub_categories.id', '=', 'assets.sub_category_id')
            ->where('assets.department_id', $departmentId)
            ->groupBy('sub_categories.id', 'sub_categories.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }

    private function getActivityLogs(Request $request, $user, $departmentId)
    {
        $query = AuditLog::with(['user' => fn($q) => $q->select('id', 'name', 'image')])
            ->latest();

        if (!$user->can('view-audit-logs') && !$user->hasRole('SuperAdmin')) {
            if ($user->hasRole('Admin') && $departmentId) {
                $departmentUserIds = User::whereHas('departments', fn($q) => $q->where('departments.id', $departmentId))->pluck('id');
                $query->whereIn('user_id', $departmentUserIds);
            } else {
                $query->where('user_id', $user->id);
            }
        }

        if ($request->filled('log_user_id')) $query->where('user_id', $request->log_user_id);
        if ($request->filled('log_action')) $query->where('action_type', $request->log_action);
        if ($request->filled('log_date')) $query->whereDate('created_at', $request->log_date);

        return $query->take(30)->get()->map(fn($log) => [
            'id' => $log->id,
            'action' => $log->action_type,
            'description' => $log->module . ' ' . $log->action_type,
            'properties' => [
                'attributes' => $log->new_values,
                'old' => $log->old_values
            ],
            'created_at' => $log->created_at,
            'user' => $log->user ? [
                'id' => $log->user->id,
                'name' => $log->user->name,
                'profile_photo_url' => $log->user->image_url,
                'profile_photo_path' => $log->user->image,
            ] : null,
        ]);
    }

    private function getTopContributors($departmentId)
    {
        if (!$departmentId) return [];

        $topContributorsRaw = Asset::select('created_by_id', DB::raw('COUNT(*) as total'))
            ->where('department_id', $departmentId)
            ->whereNotNull('created_by_id')
            ->with('creator.roles')
            ->groupBy('created_by_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();
        
        $grandTotalAssets = Asset::where('department_id', $departmentId)->count() ?: 1;

        return $topContributorsRaw->map(fn($item) => [
            'user_id' => $item->created_by_id,
            'name' => $item->creator?->name ?? 'Unknown User',
            'role' => $item->creator && $item->creator->roles->isNotEmpty() ? $item->creator->roles->first()->name : 'User',
            'profile_photo_url' => $item->creator?->image_url, 
            'profile_photo_path' => $item->creator?->image, 
            'total' => $item->total,
            'percentage' => round(($item->total / $grandTotalAssets) * 100, 1),
        ]);
    }
}
