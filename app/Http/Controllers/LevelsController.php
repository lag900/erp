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

        $levels = Level::whereHas('building') // Scope to visible buildings
            ->with('building.location')
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
        $buildings = Building::with('location') // Building Model Scope applies
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
        
         // Validation check: ensure building is accessible
         if (!Building::find($data['building_id'])) {
             return back()->with('error', 'Invalid building selected.');
         }

        Level::create($data);

        return redirect()->route('levels.index');
    }

    public function edit(Level $level): Response
    {
        $level->load('building');
        if (!$level->building) {
            abort(403, 'Unauthorized access to this level.');
        }

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

         // Validation check: ensure building is accessible
         if (!Building::find($data['building_id'])) {
             return back()->with('error', 'Invalid building selected.');
         }

        $level->update($data);

        return redirect()->route('levels.index');
    }

    public function destroy(Level $level): RedirectResponse
    {
        \Illuminate\Support\Facades\Gate::authorize('level-delete');

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($level) {
                $level->delete();
            });

            return redirect()->route('levels.index')
                ->with('message', 'Level and all related rooms/assets have been successfully decommissioned.');
        } catch (\Exception $e) {
            return redirect()->route('levels.index')
                ->with('error', 'Failed to decommission level: ' . $e->getMessage());
        }
    }
}
