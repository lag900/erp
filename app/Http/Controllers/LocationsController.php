<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLocationRequest;
use App\Http\Requests\UpdateLocationRequest;
use App\Models\Location;
use App\Services\FileService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationsController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request): Response
    {
        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name', 'arabic_name', 'description', 'image'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => $location->name,
                'arabic_name' => $location->arabic_name,
                'description' => $location->description,
                'image_url' => $location->image_url,
            ]);

        return Inertia::render('Locations/Index', [
            'locations' => $locations,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Locations/Create');
    }

    public function show(Location $location): Response
    {
        $location->load(['buildings' => function($query) {
            $query->withCount('rooms');
        }]);

        return Inertia::render('Locations/Show', [
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'arabic_name' => $location->arabic_name,
                'description' => $location->description,
                'image_url' => $location->image_url,
                'buildings' => $location->buildings->map(fn($b) => [
                    'id' => $b->id,
                    'name' => $b->name,
                    'code' => $b->code,
                    'rooms_count' => $b->rooms_count,
                 ]),
            ],
        ]);
    }

    public function store(StoreLocationRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('image')) {
            $data['image'] = $this->fileService->updateFile($request->file('image'), 'locations');
        }

        Location::create($data);

        return redirect()->route('locations.index')->with('success', 'Location created successfully.');
    }

    public function edit(Location $location): Response
    {
        return Inertia::render('Locations/Edit', [
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'arabic_name' => $location->arabic_name,
                'description' => $location->description,
                'image_url' => $location->image_url,
            ],
        ]);
    }

    public function update(UpdateLocationRequest $request, Location $location)
    {
        $data = $request->validated();
        unset($data['image']);

        if ($request->hasFile('image')) {
            $data['image'] = $this->fileService->updateFile($request->file('image'), 'locations', $location->image);
        }

        $location->update($data);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location, Request $request)
    {
        if ($location->image) {
            $this->fileService->deleteFile($location->image);
        }

        $location->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Location deleted successfully.']);
        }

        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
}
