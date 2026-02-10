<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\Room;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoomsController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        $rooms = Room::whereHas('level.building') // Scope to visible buildings
            ->with('level.building.location')
            ->orderBy('name')
            ->get()
            ->map(function (Room $room) {
                $level = $room->level;
                $building = $level?->building;
                $location = $building?->location;

                return [
                    'id' => $room->id,
                    'name' => $room->name,
                    'code' => $room->code,
                    'level' => $level?->name,
                    'building' => $building?->name,
                    'location' => $location?->name,
                ];
            });

        return Inertia::render('Rooms/Index', [
            'rooms' => $rooms,
        ]);
    }

    public function create(): Response
    {
        $levels = Level::whereHas('building') // Scope to visible buildings
            ->with('building.location')
            ->orderBy('name')
            ->get()
            ->map(function (Level $level) {
                $building = $level->building;
                $location = $building?->location;
                $parts = array_filter([
                    $location?->name,
                    $building?->name,
                    $level->name,
                ]);

                return [
                    'id' => $level->id,
                    'label' => implode(' - ', $parts),
                ];
            });

        return Inertia::render('Rooms/Create', [
            'levels' => $levels,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
        ]);
        
        // Validation check: ensure level is accessible
        $exists = Level::where('id', $data['level_id'])->whereHas('building')->exists();
        if (!$exists) {
             return back()->with('error', 'Invalid level selected.');
        }

        Room::create($data);

        return redirect()->route('rooms.index');
    }

    public function edit(Room $room): Response
    {
        // Ensure room itself is accessible (via its building)
        if (!$room->level->building) { // Building relation returns null if not in scope?
             // Actually, if we retrieved Room via implicit binding, it might not have scope applied?
             // Room doesn't have scope.
             // We should check manually.
             // But if Room doesn't have scope, user can View it.
             // We should better apply Global Scope to Room/Level? 
             // For now, explicit check:
        }
        
        // Better: ensure the building of the room is visible
        $room->load('level.building');
        if (!$room->level?->building) {
            abort(403, 'Unauthorized access to this room.');
        }

        $levels = Level::whereHas('building')
            ->with('building.location')
            ->orderBy('name')
            ->get()
            ->map(function (Level $level) {
                $building = $level->building;
                $location = $building?->location;
                $parts = array_filter([
                    $location?->name,
                    $building?->name,
                    $level->name,
                ]);

                return [
                    'id' => $level->id,
                    'label' => implode(' - ', $parts),
                ];
            }); 
            
        return Inertia::render('Rooms/Edit', [
            'room' => $room->only('id', 'name', 'code', 'level_id'),
            'levels' => $levels,
        ]);
    }

    public function update(Request $request, Room $room): RedirectResponse
    {
        $data = $request->validate([
            'level_id' => ['required', 'integer', 'exists:levels,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
        ]);
        
        // Validation check: ensure level is accessible
        $exists = Level::where('id', $data['level_id'])->whereHas('building')->exists();
        if (!$exists) {
             return back()->with('error', 'Invalid level selected.');
        }

        $room->update($data);

        return redirect()->route('rooms.index');
    }

    public function destroy(Room $room): RedirectResponse
    {
        // Check access before delete
        $room->load('level.building');
        if (!$room->level?->building) {
            abort(403, 'Unauthorized access to this room.');
        }
        
        $room->delete();

        return redirect()->route('rooms.index');
    }
    
    public function show(Room $room, Request $request): Response
    {
        $room->load(['level.building.location']);
        
        if (!$room->level?->building) {
            abort(403, 'Unauthorized access to this room.');
        }

        $authUser = $request->user();
        $isGlobalAdmin = session('is_admin_department') || 
                         ($authUser->hasRole('SuperAdmin') && $authUser->departments()->where('departments.code', 'ADMIN')->exists());
        $departmentId = session('selected_department_id');

        // Fetch assets in this room
        // We need to group them by Category -> SubCategory
        
        // Base query for assets in this room
        $assetsQuery = \App\Models\Asset::where('room_id', $room->id)
            ->with(['category', 'subCategory', 'department']);

        // Apply visibility filter if not global admin
        if (!$isGlobalAdmin) {
            // Filter assets by department visibility setup
            // Similar to CategoriesController logic: user can only see assets if they have access to the category
            // But per request: "If I am IT, I see IT stuff"
            // So we filter assets where the asset's department_id matches user's context OR user's allowed departments?
            // "if I am from IT administration, I see things based on my administration"
            
            // Logic: 
            // 1. Filter assets where asset->category is visible to current department
            // 2. OR simpler: Filter assets where asset->department_id matches current user's department? 
            //    -> Typically assets belong to a department. 
            //    -> If Asset A belongs to IT, and I am IT, I see it.
            //    -> If Asset B belongs to HR, and I am IT, I DO NOT see it? 
            //    -> Request says: "see things based on his administration"
            
            if ($departmentId) {
                // Precise context
                $assetsQuery->where('department_id', $departmentId);
            } else {
                // Broad context
                $myDeptIds = $authUser->departments->pluck('id');
                $assetsQuery->whereIn('department_id', $myDeptIds);
            }
        }

        $assets = $assetsQuery->get();

        // Transform into hierarchy: Category -> SubCategories -> Assets (Count/List)
        $inventory = $assets->groupBy('category_id')->map(function ($categoryAssets) {
            $category = $categoryAssets->first()->category;

            if (!$category) return null;
            
            return [
                'id' => $category->id,
                'name' => $category->name,
                'image_url' => $category->image_url,
                'total' => $categoryAssets->count(),
                'sub_categories' => $categoryAssets->groupBy('sub_category_id')->map(function ($subAssets) {
                    $sub = $subAssets->first()->subCategory;
                    return [
                        'id' => $sub?->id, // SubCategory might be null?
                        'name' => $sub?->name ?? 'Uncategorized',
                        'image_url' => $sub?->image_url,
                        'total' => $subAssets->count(),
                        // 'assets' => $subAssets // Uncomment if individual assets needed
                    ];
                })->values()
            ];
        })->filter()->values();

        return Inertia::render('Rooms/Show', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'code' => $room->code,
                'level' => $room->level->name,
                'building' => $room->level->building->name,
                'building_id' => $room->level->building_id,
                'location' => $room->level->building->location->name,
                'image_url' => $room->level->building->image_url, // Fallback to building image
            ],
            'inventory' => $inventory,
            'stats' => [
                'total_assets' => $assets->count(),
                'total_value' => 0, // Placeholder
            ]
        ]);
    }

    public function getByBuilding(Request $request): \Illuminate\Http\JsonResponse
    {
        $buildingId = $request->query('building_id');
        if (!$buildingId) return response()->json([]);

        // Ensure building is accessible (Global Scope on Building model handles this)
        $building = \App\Models\Building::find($buildingId);
        
        if (!$building) {
            return response()->json([]);
        }

        // Now query rooms, strictly linked to this building
        $rooms = Room::whereHas('level', fn($q) => $q->where('building_id', $buildingId))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rooms);
    }
}
