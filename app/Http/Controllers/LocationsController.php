<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LocationsController extends Controller
{
    public function index(): Response
    {
        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name', 'description']);

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
        ]);

        Location::create($data);

        return redirect()->route('locations.index');
    }

    public function edit(Location $location): Response
    {
        return Inertia::render('Locations/Edit', [
            'location' => $location->only('id', 'name', 'description'),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $location->update($data);

        return redirect()->route('locations.index');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('locations.index');
    }
}
