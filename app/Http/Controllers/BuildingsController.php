<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BuildingsController extends Controller
{
    public function index(): Response
    {
        $buildings = Building::with('location')
            ->orderBy('name')
            ->get()
            ->map(fn (Building $building) => [
                'id' => $building->id,
                'name' => $building->name,
                'code' => $building->code,
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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
        ]);

        Building::create($data);

        return redirect()->route('buildings.index');
    }

    public function edit(Building $building): Response
    {
        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Buildings/Edit', [
            'building' => $building->only('id', 'name', 'code', 'location_id'),
            'locations' => $locations,
        ]);
    }

    public function update(Request $request, Building $building): RedirectResponse
    {
        $data = $request->validate([
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:100'],
        ]);

        $building->update($data);

        return redirect()->route('buildings.index');
    }

    public function destroy(Building $building): RedirectResponse
    {
        $building->delete();

        return redirect()->route('buildings.index');
    }
}
