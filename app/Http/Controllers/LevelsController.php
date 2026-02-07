<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Level;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LevelsController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        $levels = Level::with('building.location')
            ->orderBy('level_number')
            ->orderBy('name')
            ->get()
            ->map(function (Level $level) {
                $building = $level->building;
                $location = $building?->location;

                return [
                    'id' => $level->id,
                    'name' => $level->name,
                    'level_number' => $level->level_number,
                    'building' => $building?->name,
                    'location' => $location?->name,
                ];
            });

        return Inertia::render('Levels/Index', [
            'levels' => $levels,
        ]);
    }

    public function create(): Response
    {
        $buildings = Building::with('location')
            ->orderBy('name')
            ->get()
            ->map(function (Building $building) {
                $location = $building->location;
                $label = $location
                    ? "{$location->name} - {$building->name}"
                    : $building->name;

                return [
                    'id' => $building->id,
                    'label' => $label,
                ];
            });

        return Inertia::render('Levels/Create', [
            'buildings' => $buildings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'name' => ['required', 'string', 'max:255'],
            'level_number' => ['nullable', 'integer'],
        ]);

        Level::create($data);

        return redirect()->route('levels.index');
    }

    public function edit(Level $level): Response
    {
        $buildings = Building::with('location')
            ->orderBy('name')
            ->get()
            ->map(function (Building $building) {
                $location = $building->location;
                $label = $location
                    ? "{$location->name} - {$building->name}"
                    : $building->name;

                return [
                    'id' => $building->id,
                    'label' => $label,
                ];
            });

        return Inertia::render('Levels/Edit', [
            'level' => $level->only('id', 'name', 'level_number', 'building_id'),
            'buildings' => $buildings,
        ]);
    }

    public function update(Request $request, Level $level): RedirectResponse
    {
        $data = $request->validate([
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'name' => ['required', 'string', 'max:255'],
            'level_number' => ['nullable', 'integer'],
        ]);

        $level->update($data);

        return redirect()->route('levels.index');
    }

    public function destroy(Level $level): RedirectResponse
    {
        $level->delete();

        return redirect()->route('levels.index');
    }
}
