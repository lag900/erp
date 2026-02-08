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

        $rooms = Room::with('level.building.location')
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
        $levels = Level::with('building.location')
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

        Room::create($data);

        return redirect()->route('rooms.index');
    }

    public function edit(Room $room): Response
    {
        $levels = Level::with('building.location')
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

        $room->update($data);

        return redirect()->route('rooms.index');
    }

    public function destroy(Room $room): RedirectResponse
    {
        $room->delete();

        return redirect()->route('rooms.index');
    }
    public function getByBuilding(Request $request): \Illuminate\Http\JsonResponse
    {
        $buildingId = $request->query('building_id');
        if (!$buildingId) return response()->json([]);

        $rooms = Room::whereHas('level', fn($q) => $q->where('building_id', $buildingId))
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json($rooms);
    }
}
