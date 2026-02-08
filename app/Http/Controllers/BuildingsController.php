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

    public function create(): Response
    {
        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Buildings/Create', [
            'locations' => $locations,
        ]);
    }

    public function show(Building $building): Response
    {
        $building->loadCount('rooms');
        
        return Inertia::render('Buildings/Show', [
            'building' => [
                'id' => $building->id,
                'name' => $building->name,
                'code' => $building->code,
                'image_url' => $building->image_url,
                'location_id' => $building->location_id,
                'rooms_count' => $building->rooms_count,
            ],
        ]);
    }

    public function store(StoreBuildingRequest $request)
    {
        $data = $request->validated();

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

        Building::create($data);

        return redirect()->route('buildings.index')->with('success', 'Building created successfully.');
    }

    public function edit(Building $building): Response
    {
        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Buildings/Edit', [
            'building' => $building->only('id', 'name', 'name_en', 'name_ar', 'code', 'is_shared', 'location_id', 'image_url'),
            'locations' => $locations,
        ]);
    }

    public function update(UpdateBuildingRequest $request, Building $building)
    {
        $data = $request->validated();
        unset($data['image']);
        $data['name'] = $data['name_en'];

        if ($request->hasFile('image')) {
            $data['image'] = $this->fileService->updateFile($request->file('image'), 'buildings', $building->image);
        }

        $building->update($data);

        return redirect()->route('buildings.index')->with('success', 'Building updated successfully.');
    }

    public function destroy(Building $building, Request $request)
    {
        if ($building->image) {
            $this->fileService->deleteFile($building->image);
        }
        $building->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Building deleted successfully.']);
        }

        return redirect()->route('buildings.index')->with('success', 'Building deleted successfully.');
    }
}
