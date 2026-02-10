<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBuildingRequest;
use App\Http\Requests\UpdateBuildingRequest;
use App\Models\Building;
use App\Models\Location;
use App\Services\FileService;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuildingsController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request): Response
    {
        $buildings = Building::with(['location'])
            ->orderBy('name')
            ->get()
            ->map(fn (Building $building) => [
                'id' => $building->id,
                'name' => $building->display_name,
                'code' => $building->code,
                'image_url' => $building->image_url,
                'location' => $building->location?->name,
            ]);

        return Inertia::render('Buildings/Index', [
            'buildings' => $buildings,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorizeAdministration($request->user());

        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = \App\Models\Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Buildings/Create', [
            'locations' => $locations,
            'departments' => $departments,
        ]);
    }

    public function show(Building $building): Response
    {
        $building->load(['levels.rooms' => function ($query) {
            $query->orderBy('name');
        }]);
        $building->loadCount('rooms');
        
        return Inertia::render('Buildings/Show', [
            'building' => [
                'id' => $building->id,
                'name' => $building->name,
                'code' => $building->code,
                'image_url' => $building->image_url,
                'location_id' => $building->location_id,
                'rooms_count' => $building->rooms_count,
                'levels' => $building->levels->sortBy('level_number')->values()->map(function ($level) {
                    return [
                        'id' => $level->id,
                        'name' => $level->name,
                        'rooms' => $level->rooms->map(function ($room) {
                            return [
                                'id' => $room->id,
                                'name' => $room->name,
                                'code' => $room->code,
                            ];
                        }),
                    ];
                }),
            ],
        ]);
    }

    public function store(StoreBuildingRequest $request)
    {
        $this->authorizeAdministration($request->user());

        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $data = $request->validated();
                $departmentIds = $data['department_ids'] ?? [];
                unset($data['department_ids']);

                // Generate building code
                $data['code'] = CodeGeneratorService::generateBuildingCode(
                    $data['name_en'],
                    $data['location_id']
                );

                // Name field for backward compatibility
                $data['name'] = $data['name_en'];

                if ($request->hasFile('image')) {
                    $data['image'] = $this->fileService->updateFile($request->file('image'), 'buildings');
                }

                $building = Building::create($data);
                $building->departments()->sync($departmentIds);

                return redirect()->route('buildings.index')->with('success', 'Building created successfully.');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Building creation failure: ' . $e->getMessage());
            return back()->with('error', 'Critical error during building registration. Please try again.');
        }
    }

    public function edit(Request $request, Building $building): Response
    {
        $this->authorizeAdministration($request->user());

        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $departments = \App\Models\Department::select('id', 'name')->orderBy('name')->get();

        return Inertia::render('Buildings/Edit', [
            'building' => [
                ...$building->only('id', 'name', 'name_en', 'name_ar', 'code', 'is_shared', 'location_id', 'image_url'),
                'department_ids' => $building->departments->pluck('id'),
            ],
            'locations' => $locations,
            'departments' => $departments,
        ]);
    }

    public function update(UpdateBuildingRequest $request, Building $building)
    {
        $this->authorizeAdministration($request->user());

        $data = $request->validated();
        $departmentIds = $data['department_ids'] ?? [];
        unset($data['department_ids']);
        unset($data['image']);
        $data['name'] = $data['name_en'];

        if ($request->hasFile('image')) {
            $data['image'] = $this->fileService->updateFile($request->file('image'), 'buildings', $building->image);
        }

        $building->update($data);
        $building->departments()->sync($departmentIds);

        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building, Request $request)
    {
        $this->authorizeAdministration($request->user());

        if ($building->image) {
            $this->fileService->deleteFile($building->image);
        }
        $building->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Building deleted successfully.']);
        }

        return redirect()->route('buildings.index')->with('success', 'Building deleted successfully.');
    }

    private function authorizeAdministration($user) {
        $isGlobalAdmin = session('is_admin_department') || 
                         ($user->hasRole('SuperAdmin') && $user->departments()->where('departments.code', 'ADMIN')->exists());
                         
        abort_unless($isGlobalAdmin, 403, 'Restricted: Only Administration can manage buildings.');
    }
}
