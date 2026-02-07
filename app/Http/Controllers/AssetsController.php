<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetInfo;
use App\Models\Category;
use App\Models\Room;
use App\Models\SubCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class AssetsController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        $assets = Asset::with([
            'room.level.building.location',
            'subCategory.category',
        ])
            ->orderByDesc('id')
            ->get()
            ->map(function (Asset $asset) {
                $room = $asset->room;
                $level = $room?->level;
                $building = $level?->building;
                $location = $building?->location;

                return [
                    'id' => $asset->id,
                    'note' => $asset->note,
                    'serial_number' => $asset->serial_number,
                    'condition' => $asset->condition,
                    'is_shared' => (bool) $asset->is_shared,
                    'count' => $asset->count ?? 1,
                    'category' => $asset->subCategory?->category?->name,
                    'subCategory' => $asset->subCategory?->name,
                    'owner_department' => $asset->department?->name,
                    'room' => [
                        'name' => $room?->name,
                        'code' => $room?->code,
                        'level' => $level?->name,
                        'building' => $building?->name,
                        'location' => $location?->name,
                    ],
                ];
            });

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
        ]);
    }

    public function create(Request $request): Response
    {
        $rooms = Room::with('level.building.location')
            ->orderBy('name')
            ->get()
            ->map(function (Room $room) {
                $level = $room->level;
                $building = $level?->building;
                $location = $building?->location;
                $parts = array_filter([
                    $location?->name,
                    $building?->name,
                    $level?->name,
                    $room->name,
                ]);

                return [
                    'id' => $room->id,
                    'label' => implode(' - ', $parts),
                ];
            });

        $subCategories = SubCategory::with('category')
            ->orderBy('name')
            ->get()
            ->map(function (SubCategory $subCategory) {
                $categoryName = $subCategory->category?->name ?? '';
                $label = $categoryName 
                    ? "{$categoryName} - {$subCategory->name}"
                    : $subCategory->name;

                return [
                    'id' => (int) $subCategory->id,
                    'label' => (string) $label,
                ];
            });

        $roomAssetsSummary = [];
        if ($request->has('room_id')) {
            $roomId = $request->input('room_id');
            $roomAssetsSummary = Asset::where('room_id', $roomId)
                ->with('subCategory.category')
                ->get()
                ->groupBy('sub_category_id')
                ->map(function ($assets) {
                    $firstAsset = $assets->first();
                    return [
                        'subcategory_id' => $firstAsset->sub_category_id,
                        'subcategory_name' => $firstAsset->subCategory?->name ?? 'Unknown',
                        'category_name' => $firstAsset->subCategory?->category?->name ?? 'Unknown',
                        'total_count' => $assets->sum('count'),
                        'asset_count' => $assets->count(),
                    ];
                })
                ->values()
                ->sortBy('subcategory_name')
                ->values()
                ->toArray();
        }

        $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Assets/Create', [
            'rooms' => $rooms,
            'subCategories' => $subCategories,
            'departments' => $departments,
            'roomAssetsSummary' => $roomAssetsSummary,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $departmentId = $request->session()->get('selected_department_id');

        abort_unless($departmentId, 403);

        $entryType = $request->input('entry_type');
        
        $rules = [
            'entry_type' => ['required', 'in:individual,series'],
        ];
        
        if ($entryType === 'individual') {
            $rules['room_id'] = ['required', 'integer', 'exists:rooms,id'];
            $rules['sub_category_id'] = ['required', 'integer', 'exists:sub_categories,id'];
            $rules['note'] = ['nullable', 'string'];
            $rules['serial_number'] = ['nullable', 'string', 'max:255'];
            $rules['condition'] = ['required', 'in:active,maintenance,damaged,disposed'];
            $rules['is_shared'] = ['required', 'boolean'];
            $rules['shared_department_ids'] = ['array'];
            $rules['shared_department_ids.*'] = ['integer', 'exists:departments,id'];
            $rules['count'] = ['required', 'integer', 'min:1'];
            $rules['infos'] = ['array'];
            $rules['infos.*.key'] = ['nullable', 'string'];
            $rules['infos.*.value'] = ['nullable', 'string'];
            $rules['infos.*.image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
        } else {
            $rules['base_asset'] = ['required', 'array'];
            $rules['base_asset.room_id'] = ['required', 'integer', 'exists:rooms,id'];
            $rules['base_asset.sub_category_id'] = ['required', 'integer', 'exists:sub_categories,id'];
            $rules['base_asset.note'] = ['nullable', 'string'];
            $rules['base_asset.condition'] = ['required', 'in:active,maintenance,damaged,disposed'];
            $rules['base_asset.is_shared'] = ['required', 'boolean'];
            $rules['base_asset.shared_department_ids'] = ['array'];
            $rules['peered_assets'] = ['array'];
            $rules['peered_assets.*.sub_category_id'] = ['required', 'integer', 'exists:sub_categories,id'];
            $rules['peered_assets.*.serial_number'] = ['nullable', 'string', 'max:255'];
            $rules['peered_assets.*.infos'] = ['array'];
            $rules['peered_assets.*.infos.*.key'] = ['nullable', 'string'];
            $rules['peered_assets.*.infos.*.value'] = ['nullable', 'string'];
            $rules['peered_assets.*.infos.*.image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
        }
        
        $data = $request->validate($rules);

        DB::transaction(function () use ($data, $departmentId, $request) {
            if ($data['entry_type'] === 'individual') {
                // Individual entry
                $asset = Asset::create([
                    'department_id' => $departmentId,
                    'room_id' => $data['room_id'],
                    'sub_category_id' => $data['sub_category_id'],
                    'note' => $data['note'] ?? null,
                    'count' => $data['count'] ?? 1,
                    'serial_number' => $data['serial_number'] ?? null,
                    'condition' => $data['condition'],
                    'is_shared' => $data['is_shared'],
                    'created_by_id' => $request->user()->id,
                ]);

                if ($data['is_shared'] && !empty($data['shared_department_ids'])) {
                    $asset->sharedDepartments()->sync($data['shared_department_ids']);
                }

                $infos = [];
                foreach ($data['infos'] ?? [] as $index => $info) {
                    if (empty($info['key'])) {
                        continue;
                    }

                    $imagePath = null;
                    if ($request->hasFile("infos.{$index}.image")) {
                        $imagePath = $request->file("infos.{$index}.image")->store('asset_infos', 'public');
                    }

                    $infos[] = [
                        'asset_id' => $asset->id,
                        'key' => $info['key'],
                        'value' => $info['value'] ?? null,
                        'image' => $imagePath,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($infos)) {
                    AssetInfo::insert($infos);
                }
            } else {
                // Series entry
                $baseAsset = Asset::create([
                    'department_id' => $departmentId,
                    'room_id' => $data['base_asset']['room_id'],
                    'sub_category_id' => $data['base_asset']['sub_category_id'],
                    'note' => $data['base_asset']['note'] ?? null,
                    'count' => 1, // Default count for base asset
                    'condition' => $data['base_asset']['condition'],
                    'is_shared' => $data['base_asset']['is_shared'],
                    'created_by_id' => $request->user()->id,
                ]);

                if ($data['base_asset']['is_shared'] && !empty($data['base_asset']['shared_department_ids'])) {
                    $baseAsset->sharedDepartments()->sync($data['base_asset']['shared_department_ids']);
                }

                // Create peered assets linked to base asset
                foreach ($data['peered_assets'] ?? [] as $peeredIndex => $peeredData) {
                    $peeredAsset = Asset::create([
                        'department_id' => $departmentId,
                        'room_id' => $data['base_asset']['room_id'], // Use base asset room_id
                        'sub_category_id' => $peeredData['sub_category_id'],
                        'count' => 1, // Default count for peered assets
                        'peered_asset_id' => $baseAsset->id,
                        'serial_number' => $peeredData['serial_number'] ?? null,
                        'condition' => $data['base_asset']['condition'], // Peers inherit condition and sharing from base in shared entries
                        'is_shared' => $data['base_asset']['is_shared'],
                        'created_by_id' => $request->user()->id,
                    ]);

                    if ($data['base_asset']['is_shared'] && !empty($data['base_asset']['shared_department_ids'])) {
                        $peeredAsset->sharedDepartments()->sync($data['base_asset']['shared_department_ids']);
                    }

                    // Save infos for each peered asset
                    $peeredInfos = [];
                    foreach ($peeredData['infos'] ?? [] as $infoIndex => $info) {
                        if (empty($info['key'])) {
                            continue;
                        }

                        $imagePath = null;
                        if ($request->hasFile("peered_assets.{$peeredIndex}.infos.{$infoIndex}.image")) {
                            $imagePath = $request->file("peered_assets.{$peeredIndex}.infos.{$infoIndex}.image")->store('asset_infos', 'public');
                        }

                        $peeredInfos[] = [
                            'asset_id' => $peeredAsset->id,
                            'key' => $info['key'],
                            'value' => $info['value'] ?? null,
                            'image' => $imagePath,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    if (!empty($peeredInfos)) {
                        AssetInfo::insert($peeredInfos);
                    }
                }
            }
        });

        return redirect()->route('assets.index');
    }

    public function show(Request $request, Asset $asset): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        abort_unless($departmentId && $asset->department_id === $departmentId, 403);

        $asset->load([
            'room.level.building.location',
            'subCategory.category',
            'infos',
        ]);

        $room = $asset->room;
        $level = $room?->level;
        $building = $level?->building;
        $location = $building?->location;

        // Get summary of assets by subcategory in this room
        $roomAssetsSummary = Asset::where('room_id', $asset->room_id)
            ->where('department_id', $departmentId)
            ->with('subCategory.category')
            ->get()
            ->groupBy('sub_category_id')
            ->map(function ($assets) {
                $firstAsset = $assets->first();
                return [
                    'subcategory_id' => $firstAsset->sub_category_id,
                    'subcategory_name' => $firstAsset->subCategory?->name ?? 'Unknown',
                    'category_name' => $firstAsset->subCategory?->category?->name ?? 'Unknown',
                    'total_count' => $assets->sum('count'),
                    'asset_count' => $assets->count(),
                ];
            })
            ->values()
            ->sortBy('subcategory_name')
            ->values();

        return Inertia::render('Assets/Show', [
            'asset' => [
                'id' => $asset->id,
                'note' => $asset->note,
                'count' => $asset->count ?? 1,
                'serial_number' => $asset->serial_number,
                'condition' => $asset->condition,
                'is_shared' => (bool) $asset->is_shared,
                'peered_asset_id' => $asset->peered_asset_id,
                'category' => $asset->subCategory?->category?->name,
                'subCategory' => $asset->subCategory?->name,
                'owner_department' => $asset->department?->name,
                'room' => [
                    'id' => $room?->id,
                    'name' => $room?->name,
                    'code' => $room?->code,
                    'level' => $level?->name,
                    'building' => $building?->name,
                    'location' => $location?->name,
                ],
                'infos' => $asset->infos->map(function ($info) {
                    return [
                        'id' => $info->id,
                        'key' => $info->key,
                        'value' => $info->value,
                        'image_url' => $info->image_url,
                    ];
                }),
                'shared_departments' => $asset->sharedDepartments->pluck('name')->values(),
                'movements' => $asset->movements()->with(['fromRoom', 'toRoom', 'fromDepartment', 'toDepartment', 'user'])->orderByDesc('created_at')->get(),
            ],
            'roomAssetsSummary' => $roomAssetsSummary,
        ]);
    }

    public function edit(Request $request, Asset $asset): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        $asset->load(['infos', 'sharedDepartments']);

        $rooms = Room::with('level.building.location')
            ->orderBy('name')
            ->get()
            ->map(function (Room $room) {
                $level = $room->level;
                $building = $level?->building;
                $location = $building?->location;
                $parts = array_filter([
                    $location?->name,
                    $building?->name,
                    $level?->name,
                    $room->name,
                ]);

                return [
                    'id' => $room->id,
                    'label' => implode(' - ', $parts),
                ];
            });

        $subCategories = SubCategory::with('category')
            ->orderBy('name')
            ->get()
            ->map(function (SubCategory $subCategory) {
                $categoryName = $subCategory->category?->name;

                return [
                    'id' => $subCategory->id,
                    'label' => $categoryName
                        ? "{$categoryName} - {$subCategory->name}"
                        : $subCategory->name,
                ];
            });
            
        $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Assets/Edit', [
            'asset' => [
                'id' => $asset->id,
                'room_id' => $asset->room_id,
                'sub_category_id' => $asset->sub_category_id,
                'note' => $asset->note,
                'count' => $asset->count ?? 1,
                'serial_number' => $asset->serial_number,
                'condition' => $asset->condition,
                'is_shared' => (bool) $asset->is_shared,
                'shared_department_ids' => $asset->sharedDepartments->pluck('id')->values(),
                'infos' => $asset->infos->map(function ($info) {
                    return [
                        'id' => $info->id,
                        'key' => $info->key,
                        'value' => $info->value,
                        'image_url' => $info->image_url,
                    ];
                }),
            ],
            'rooms' => $rooms,
            'subCategories' => $subCategories,
            'departments' => $departments,
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $departmentId = $request->session()->get('selected_department_id');

        abort_unless($departmentId && $asset->department_id === $departmentId, 403);

        $data = $request->validate([
            'room_id' => ['required', 'integer', 'exists:rooms,id'],
            'sub_category_id' => ['required', 'integer', 'exists:sub_categories,id'],
            'note' => ['nullable', 'string'],
            'count' => ['required', 'integer', 'min:1'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'condition' => ['required', 'in:active,maintenance,damaged,disposed'],
            'is_shared' => ['required', 'boolean'],
            'shared_department_ids' => ['array'],
            'shared_department_ids.*' => ['integer', 'exists:departments,id'],
            'infos' => ['array'],
            'infos.*.id' => ['nullable', 'integer', 'exists:asset_infos,id'],
            'infos.*.key' => ['nullable', 'string'],
            'infos.*.value' => ['nullable', 'string'],
            'infos.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        $oldRoomId = $asset->room_id;
        $oldDepartmentId = $asset->department_id;

        DB::transaction(function () use ($data, $asset, $request, $oldRoomId, $oldDepartmentId) {
            $asset->update([
                'room_id' => $data['room_id'],
                'sub_category_id' => $data['sub_category_id'],
                'note' => $data['note'] ?? null,
                'count' => $data['count'] ?? 1,
                'serial_number' => $data['serial_number'] ?? null,
                'condition' => $data['condition'],
                'is_shared' => $data['is_shared'],
            ]);

            if ($data['is_shared']) {
                $asset->sharedDepartments()->sync($data['shared_department_ids'] ?? []);
            } else {
                $asset->sharedDepartments()->detach();
            }

            // Record movement if room changed
            if ($oldRoomId != $data['room_id']) {
                \App\Models\AssetMovement::create([
                    'asset_id' => $asset->id,
                    'from_room_id' => $oldRoomId,
                    'to_room_id' => $data['room_id'],
                    'from_department_id' => $oldDepartmentId,
                    'to_department_id' => $oldDepartmentId,
                    'user_id' => $request->user()->id,
                    'reason' => 'Location update via Edit',
                ]);
            }

            $incoming = collect($data['infos'] ?? [])
                ->filter(fn ($info) => !empty($info['key']))
                ->values();

            $existingIds = $asset->infos()->pluck('id')->all();
            $incomingIds = $incoming->pluck('id')->filter()->all();
            $idsToDelete = array_diff($existingIds, $incomingIds);

            if (!empty($idsToDelete)) {
                $infosToDelete = AssetInfo::whereIn('id', $idsToDelete)->get();
                foreach ($infosToDelete as $infoToDelete) {
                    if ($infoToDelete->image && !str_starts_with($infoToDelete->image, 'http')) {
                        Storage::disk('public')->delete($infoToDelete->image);
                    }
                }
                AssetInfo::whereIn('id', $idsToDelete)->delete();
            }

            foreach ($incoming as $index => $info) {
                $imagePath = null;
                
                // Handle file upload
                if ($request->hasFile("infos.{$index}.image")) {
                    // Delete old image if exists
                    if (!empty($info['id'])) {
                        $existingInfo = AssetInfo::find($info['id']);
                        if ($existingInfo && $existingInfo->image && !str_starts_with($existingInfo->image, 'http')) {
                            Storage::disk('public')->delete($existingInfo->image);
                        }
                    }
                    $imagePath = $request->file("infos.{$index}.image")->store('asset_infos', 'public');
                } elseif (!empty($info['id'])) {
                    // Keep existing image if no new file uploaded
                    $existingInfo = AssetInfo::find($info['id']);
                    $imagePath = $existingInfo?->image;
                }

                AssetInfo::updateOrCreate(
                    ['id' => $info['id'] ?? null],
                    [
                        'asset_id' => $asset->id,
                        'key' => $info['key'],
                        'value' => $info['value'] ?? null,
                        'image' => $imagePath,
                    ]
                );
            }
        });

        return redirect()->route('assets.show', $asset);
    }

    public function destroy(Request $request, Asset $asset): RedirectResponse
    {
        $departmentId = $request->session()->get('selected_department_id');

        abort_unless($departmentId && $asset->department_id === $departmentId, 403);

        $asset->delete();

        return redirect()->route('assets.index');
    }
}
