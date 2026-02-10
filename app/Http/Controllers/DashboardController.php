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

        $isGlobal = session('is_admin_department');

        $usersQuery = User::orderBy('name');
        if (!$isGlobal && $departmentId) {
            $usersQuery->whereHas('departments', fn($q) => $q->where('departments.id', $departmentId));
        }

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
                ? $usersQuery->get(['id', 'name'])
                : [],
            'recentActivity' => $this->getRecentActivity($departmentId),
            'hasDepartmentSelected' => (bool) $departmentId,
            'hasMultipleDepartments' => $user->departments()->count() > 1,
        ]);
    }

    private function getStats($departmentId): array
    {
        $isGlobal = session('is_admin_department');

        $assetQuery = DB::table('assets')
            ->whereNull('deleted_at');

        if (!$isGlobal && $departmentId) {
            $assetQuery->where(function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId)
                  ->orWhereExists(function ($sq) use ($departmentId) {
                      $sq->select(DB::raw(1))
                         ->from('asset_department')
                         ->whereColumn('asset_department.asset_id', 'assets.id')
                         ->where('asset_department.department_id', $departmentId);
                  })
                  ->orWhereExists(function ($sq) use ($departmentId) {
                      $sq->select(DB::raw(1))
                         ->from('category_department')
                         ->whereColumn('category_department.category_id', 'assets.category_id')
                         ->where('category_department.department_id', $departmentId);
                  });
            })
            ->whereExists(function ($bq) use ($departmentId) {
                $bq->select(DB::raw(1))
                   ->from('rooms')
                   ->join('levels', 'rooms.level_id', '=', 'levels.id')
                   ->join('buildings', 'levels.building_id', '=', 'buildings.id')
                   ->join('building_department', 'buildings.id', '=', 'building_department.building_id')
                   ->whereColumn('rooms.id', 'assets.room_id')
                   ->where('building_department.department_id', $departmentId);
            });
        }

        return [
            'assets' => (int) $assetQuery->sum('count'),
            'locations' => DB::table('locations')
                ->where(function($q) use ($isGlobal, $departmentId) {
                    if (!$isGlobal && $departmentId) {
                        $q->whereExists(function($sq) use ($departmentId) {
                            $sq->select(DB::raw(1))
                               ->from('buildings')
                               ->join('building_department', 'buildings.id', '=', 'building_department.building_id')
                               ->whereColumn('buildings.location_id', 'locations.id')
                               ->where('building_department.department_id', $departmentId);
                        });
                    }
                })->count(),
            'buildings' => Building::count(), 
            'rooms' => Room::whereHas('level.building')->count(), 
            'users' => $isGlobal 
                ? User::count() 
                : ($departmentId ? User::whereHas('departments', fn ($q) => $q->where('departments.id', $departmentId))->count() : 0),
            'news' => News::count(),
        ];
    }

    private function getAssetsByCategory($departmentId)
    {
        $isGlobal = session('is_admin_department');
        
        $query = DB::table('assets')
            ->join('categories', 'assets.category_id', '=', 'categories.id')
            ->whereNull('assets.deleted_at')
            ->select(
                DB::raw('COALESCE(categories.name_ar, categories.name) as name'),
                DB::raw('SUM(assets.count) as total')
            );

        if (!$isGlobal && $departmentId) {
            $query->where(function ($q) use ($departmentId) {
                $q->where('assets.department_id', $departmentId)
                  ->orWhereExists(function ($sq) use ($departmentId) {
                      $sq->select(DB::raw(1))
                         ->from('category_department')
                         ->whereColumn('category_department.category_id', 'categories.id')
                         ->where('category_department.department_id', $departmentId);
                  });
            })
            ->whereExists(function ($bq) use ($departmentId) {
                $bq->select(DB::raw(1))
                   ->from('rooms')
                   ->join('levels', 'rooms.level_id', '=', 'levels.id')
                   ->join('buildings', 'levels.building_id', '=', 'buildings.id')
                   ->join('building_department', 'buildings.id', '=', 'building_department.building_id')
                   ->whereColumn('rooms.id', 'assets.room_id')
                   ->where('building_department.department_id', $departmentId);
            });
        }

        return $query->groupBy('categories.id', 'categories.name', 'categories.name_ar')
            ->orderByDesc('total')
            ->get();
    }

    private function getRecentActivity($departmentId)
    {
        $isGlobal = session('is_admin_department');
        
        $query = Asset::with(['category', 'subCategory', 'room.level.building.location'])
            ->whereHas('category', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('category_department')
                           ->whereColumn('category_department.category_id', 'categories.id')
                           ->where('category_department.department_id', $departmentId);
                    });
                }
            })
            ->whereHas('room.level.building', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('building_department')
                           ->whereColumn('building_department.building_id', 'buildings.id')
                           ->where('building_department.department_id', $departmentId);
                    });
                }
            })
            ->latest('updated_at');

        return $query->take(5)
            ->get()
            ->map(fn ($asset) => [
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
        $isGlobal = session('is_admin_department');
        
        $query = DB::table('assets')
            ->join('sub_categories', 'assets.sub_category_id', '=', 'sub_categories.id')
            ->join('categories', 'sub_categories.category_id', '=', 'categories.id')
            ->whereNull('assets.deleted_at')
            ->select(
                DB::raw('COALESCE(sub_categories.name_ar, sub_categories.name) as name'),
                DB::raw('SUM(assets.count) as total')
            );

        if (!$isGlobal && $departmentId) {
            $query->where(function ($q) use ($departmentId) {
                 $q->where('assets.department_id', $departmentId)
                   ->orWhereExists(function ($sq) use ($departmentId) {
                       $sq->select(DB::raw(1))
                          ->from('category_department')
                          ->whereColumn('category_department.category_id', 'categories.id')
                          ->where('category_department.department_id', $departmentId);
                   });
            })
            ->whereExists(function ($bq) use ($departmentId) {
                $bq->select(DB::raw(1))
                   ->from('rooms')
                   ->join('levels', 'rooms.level_id', '=', 'levels.id')
                   ->join('buildings', 'levels.building_id', '=', 'buildings.id')
                   ->join('building_department', 'buildings.id', '=', 'building_department.building_id')
                   ->whereColumn('rooms.id', 'assets.room_id')
                   ->where('building_department.department_id', $departmentId);
            });
        }

        return $query->groupBy('sub_categories.id', 'sub_categories.name', 'sub_categories.name_ar')
            ->orderByDesc('total')
            ->get();
    }

    private function getTopAssetGroups($departmentId)
    {
        $isGlobal = session('is_admin_department');

        $query = Asset::select('group_name', DB::raw('SUM(count) as total'))
            ->whereNotNull('group_name')
            ->whereHas('category', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('category_department')
                           ->whereColumn('category_department.category_id', 'categories.id')
                           ->where('category_department.department_id', $departmentId);
                    });
                }
            })
            ->whereHas('room.level.building', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('building_department')
                           ->whereColumn('building_department.building_id', 'buildings.id')
                           ->where('building_department.department_id', $departmentId);
                    });
                }
            });

        return $query->groupBy('group_name')
            ->orderByDesc('total')
            ->take(5)
            ->get();
    }

    private function getMostEnteredData($departmentId)
    {
        return $this->getAssetsBySubcategory($departmentId)->take(5);
    }

    private function getActivityLogs(Request $request, $user, $departmentId)
    {
        $query = AuditLog::with(['user' => fn($q) => $q->select('id', 'name', 'image')])
            ->latest();

        $isGlobal = session('is_admin_department');

        // If not Global Admin, restrict logs
        if (!$isGlobal) {
             if ($departmentId) {
                // Get users primarily from this department
                // Note: This relies on users being assigned to department to see their logs.
                // If a user did something in this department but is assigned elsewhere, it might be tricky.
                // But generally Department Admin sees actions of users IN their department.
                $departmentUserIds = User::whereHas('departments', fn($q) => $q->where('departments.id', $departmentId))->pluck('id');
                $query->whereIn('user_id', $departmentUserIds);
             } else {
                 // No department context? Show only own logs
                 $query->where('user_id', $user->id);
             }
        }
        // Global Admins see everything.

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
        $isGlobal = session('is_admin_department');
        
        $query = Asset::select('created_by_id', DB::raw('COUNT(*) as total'))
            ->whereHas('category', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('category_department')
                           ->whereColumn('category_department.category_id', 'categories.id')
                           ->where('category_department.department_id', $departmentId);
                    });
                }
            })
            ->whereHas('room.level.building', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('building_department')
                           ->whereColumn('building_department.building_id', 'buildings.id')
                           ->where('building_department.department_id', $departmentId);
                    });
                }
            })
            ->whereNotNull('created_by_id')
            ->with(['creator.roles', 'creator.departments']);
            
        $topContributorsRaw = $query->groupBy('created_by_id')
            ->orderByDesc('total')
            ->take(10)
            ->get();
        
        // Sum total assets visible to this department for percentage calculation
        $grandTotalAssets = Asset::whereHas('category', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('category_department')
                           ->whereColumn('category_department.category_id', 'categories.id')
                           ->where('category_department.department_id', $departmentId);
                    });
                }
            })
            ->whereHas('room.level.building', function ($q) use ($isGlobal, $departmentId) {
                if (!$isGlobal && $departmentId) {
                    $q->whereExists(function ($sq) use ($departmentId) {
                        $sq->select(DB::raw(1))
                           ->from('building_department')
                           ->whereColumn('building_department.building_id', 'buildings.id')
                           ->where('building_department.department_id', $departmentId);
                    });
                }
            })
            ->count();
            
        $grandTotalAssets = $grandTotalAssets ?: 1;

        return $topContributorsRaw->map(fn ($item) => [
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
