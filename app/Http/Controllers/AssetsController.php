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
            ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
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
                    'count' => $asset->count ?? 1,
                    'category' => $asset->subCategory?->category?->name,
                    'subCategory' => $asset->subCategory?->name,
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
            $departmentId = $request->session()->get('selected_department_id');
            $roomId = $request->input('room_id');
            
            $roomAssetsSummary = Asset::where('room_id', $roomId)
                ->when($departmentId, fn ($query) => $query->where('department_id', $departmentId))
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

        return Inertia::render('Assets/Create', [
            'rooms' => $rooms,
            'subCategories' => $subCategories,
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
            $rules['peered_assets'] = ['array'];
            $rules['peered_assets.*.sub_category_id'] = ['required', 'integer', 'exists:sub_categories,id'];
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
                ]);

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
                ]);

                // Create peered assets linked to base asset
                foreach ($data['peered_assets'] ?? [] as $peeredIndex => $peeredData) {
                    $peeredAsset = Asset::create([
                        'department_id' => $departmentId,
                        'room_id' => $data['base_asset']['room_id'], // Use base asset room_id
                        'sub_category_id' => $peeredData['sub_category_id'],
                        'count' => 1, // Default count for peered assets
                        'peered_asset_id' => $baseAsset->id,
                    ]);

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
                'peered_asset_id' => $asset->peered_asset_id,
                'category' => $asset->subCategory?->category?->name,
                'subCategory' => $asset->subCategory?->name,
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
            ],
            'roomAssetsSummary' => $roomAssetsSummary,
        ]);
    }

    public function edit(Request $request, Asset $asset): Response
    {
        $departmentId = $request->session()->get('selected_department_id');

        abort_unless($departmentId && $asset->department_id === $departmentId, 403);

        $asset->load(['infos']);

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

        return Inertia::render('Assets/Edit', [
            'asset' => [
                'id' => $asset->id,
                'room_id' => $asset->room_id,
                'sub_category_id' => $asset->sub_category_id,
                'note' => $asset->note,
                'count' => $asset->count ?? 1,
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
            'infos' => ['array'],
            'infos.*.id' => ['nullable', 'integer', 'exists:asset_infos,id'],
            'infos.*.key' => ['nullable', 'string'],
            'infos.*.value' => ['nullable', 'string'],
            'infos.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        DB::transaction(function () use ($data, $asset, $request) {
            $asset->update([
                'room_id' => $data['room_id'],
                'sub_category_id' => $data['sub_category_id'],
                'note' => $data['note'] ?? null,
                'count' => $data['count'] ?? 1,
            ]);

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
