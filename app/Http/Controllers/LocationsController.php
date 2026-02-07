<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationsController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description', 'image'])
            ->map(fn (Location $location) => [
                'id' => $location->id,
                'name' => $location->name,
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('locations', 'public');
        }

        Location::create($data);

        return redirect()->route('locations.index');
    }

    public function edit(Location $location): Response
    {
        return Inertia::render('Locations/Edit', [
            'location' => [
                'id' => $location->id,
                'name' => $location->name,
                'description' => $location->description,
                'image_url' => $location->image_url,
            ],
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            if ($location->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($location->image);
            }
            $data['image'] = $request->file('image')->store('locations', 'public');
        }

        $location->update($data);

        return redirect()->route('locations.index');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('locations.index');
    }
}
