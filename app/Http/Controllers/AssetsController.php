<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetInfo;
use App\Models\AssetGroupType;
use App\Models\Category;
use App\Models\Room;
use App\Models\SubCategory;
use App\Models\SpecTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Inertia\Inertia;
use Inertia\Response;

class AssetsController extends Controller
{
    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');
        $filters = $request->only(['search', 'statuses', 'building_id', 'room_id', 'category_id', 'sub_category_id', 'created_by', 'date_from', 'date_to', 'asset_type']);

        $assets = Asset::with([
            'room.level.building.location',
            'category',
            'subCategory',
            'department',
            'parent',
            'children.category', 
            'children.subCategory', 
            'children.creator',
            'infos',
            'creator'
        ])
            ->when(
                empty($filters['search']) && 
                empty($filters['category_id']) && 
                empty($filters['sub_category_id']) && 
                empty($filters['room_id']) &&
                empty($filters['building_id']) &&
                empty($filters['asset_type']) &&
                empty($filters['statuses']), 
                function ($query) {
                    $query->whereNull('parent_id');
                }
            )
            ->filter($filters)
            ->orderByRaw('series_no IS NULL')
            ->orderBy('series_no')
            ->orderBy('component_no')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString()
            ->through(fn ($asset) => [
                'id' => $asset->id,
                'asset_code' => $asset->asset_code,
                'note' => $asset->note,
                'status' => $asset->status,
                'is_shared' => (bool) $asset->is_shared,
                'is_parent' => (bool) $asset->is_parent,
                'parent_id' => $asset->parent_id,
                'parent_code' => $asset->parent?->asset_code,
                'image_url' => $asset->image_url,
                'short_code' => $asset->short_code,
                'bundle_serial' => $asset->bundle_serial,
                'full_serial' => $asset->full_serial,
                'series_no' => $asset->series_no,
                'created_by' => $asset->creator?->name ?? 'System',
                'created_at_formatted' => $asset->created_at ? $asset->created_at->format('d M Y — h:i A') : 'N/A',
                'children' => $asset->children->map(fn($c) => [
                    'id' => $c->id,
                    'name' => ($c->category?->name ?? 'Unknown') . ($c->subCategory ? " - {$c->subCategory->name}" : ""),
                    'asset_code' => $c->asset_code,
                    'status' => $c->status,
                    'image_url' => $c->image_url,
                    'short_code' => $c->short_code,
                    'bundle_serial' => $c->bundle_serial,
                    'full_serial' => $c->full_serial,
                    'series_no' => $c->series_no,
                    'created_by' => $c->creator?->name ?? 'System',
                    'created_at_formatted' => $c->created_at ? $c->created_at->format('d M Y — h:i A') : 'N/A',
                ]),
                'count' => $asset->count ?? 1,
                'category' => $asset->category?->name,
                'subCategory' => $asset->subCategory?->name,
                'owner_department' => $asset->department?->name,
                'room' => [
                    'name' => $asset->room?->name,
                    'code' => $asset->room?->code,
                    'level' => $asset->room?->level?->name,
                    'building' => $asset->room?->level?->building?->name,
                    'location' => $asset->room?->level?->building?->location?->name,
                ],
            ]);

        // Meta for filters
        $buildings = \App\Models\Building::orderBy('name')->get(['id', 'name']);
        $categories = Category::orderBy('name')->get(['id', 'name']);
        
        // Departments meta
        $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']);
        
        // Users (Creators) meta - showing only users who have created assets
        $creators = \App\Models\User::whereHas('createdAssets')->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Assets/Index', [
            'assets' => $assets,
            'filters' => $filters,
            'meta' => [
                'buildings' => $buildings,
                'categories' => $categories,
                'departments' => $departments,
                'creators' => $creators,
            ]
        ]);
    }

    public function create(Request $request): Response
    {
        try {
            $roomId = (int) $request->input('room_id');
            
            $rooms = $this->getRoomsForSelection() ?? collect([]);
            $classifications = $this->getClassificationsForSelection() ?? collect([]);
            $departments = \App\Models\Department::orderBy('name')->get(['id', 'name']) ?? collect([]);
            
            $roomAssetsSummary = [];
            $recentAdditions = [];

            if ($roomId) {
                $roomAssetsSummary = $this->getRoomAssetsSummary($roomId) ?? [];
                
                $recentAdditions = Asset::where('room_id', $roomId)
                    ->with(['category', 'subCategory', 'creator'])
                    ->orderByDesc('created_at')
                    ->take(10)
                    ->get()
                    ->map(fn($a) => [
                        'id' => $a->id,
                        'asset_code' => $a->asset_code,
                        'bundle_serial' => $a->bundle_serial,
                        'full_serial' => $a->full_serial,
                        'name' => ($a->category?->name ?? 'Unknown') . ($a->subCategory ? " - {$a->subCategory->name}" : ""),
                        'status' => $a->status,
                        'time' => $a->created_at->diffForHumans(),
                        'created_by' => $a->creator?->name ?? 'System',
                    ]);
            }

            return Inertia::render('Assets/Create', [
                'rooms' => $rooms,
                'classifications' => $classifications,
                'categories' => Category::orderBy('name')->get(['id', 'name']), // Still needed for bundle components sometimes
                'subCategories' => SubCategory::orderBy('name')->get(['id', 'name', 'category_id']), // Still needed for bundle quick add
                'departments' => $departments,
                'roomAssetsSummary' => $roomAssetsSummary,
                'recentAdditions' => $recentAdditions,
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Asset Create Critical Failure: " . $e->getMessage());
            
            // Ultimate fail-safe: return minimal valid state so page at least renders
            return Inertia::render('Assets/Create', [
                'rooms' => [],
                'classifications' => [],
                'categories' => [],
                'subCategories' => [],
                'departments' => [],
                'roomAssetsSummary' => [],
                'recentAdditions' => [],
                'errorMessage' => 'System encountered a data loading error. Please contact support if problem persists.'
            ]);
        }
    }

    private function getRoomsForSelection()
    {
        return Room::with('level.building.location')
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
    }

    private function getCategoriesForSelection()
    {
        return Category::orderBy('name')
            ->get()
            ->map(fn($c) => [
                'id' => $c->id, 
                'label' => $c->name . ($c->name_ar ? " ({$c->name_ar})" : "")
            ]);
    }

    private function getClassificationsForSelection()
    {
        $categories = Category::with('subCategories')->orderBy('name')->get();
        $classifications = collect();

        foreach ($categories as $category) {
            $catLabel = $category->name . ($category->name_ar ? " ({$category->name_ar})" : "");
            
            if ($category->subCategories->isEmpty()) {
                $classifications->push([
                    'id' => "{$category->id}:",
                    'label' => $catLabel,
                    'category_id' => $category->id,
                    'sub_category_id' => null,
                ]);
            } else {
                foreach ($category->subCategories as $sub) {
                    $subLabel = $sub->name . ($sub->name_ar ? " ({$sub->name_ar})" : "");
                    $classifications->push([
                        'id' => "{$category->id}:{$sub->id}",
                        'label' => "{$catLabel} - {$subLabel}",
                        'category_id' => $category->id,
                        'sub_category_id' => $sub->id,
                    ]);
                }
            }
        }

        return $classifications;
    }

    private function getSubCategoriesForSelection()
    {
        return SubCategory::with('category')
            ->orderBy('name')
            ->get()
            ->map(function (SubCategory $subCategory) {
                return [
                'id' => (int) $subCategory->id,
                'label' => $subCategory->name . ($subCategory->name_ar ? " ({$subCategory->name_ar})" : ""),
                'category_id' => $subCategory->category_id,
            ];    });
    }

    private function getRoomAssetsSummary($roomId)
    {
        return Asset::where('room_id', $roomId)
            ->with(['category', 'subCategory'])
            ->get()
            ->groupBy('category_id')
            ->map(function ($assets) {
                $firstAsset = $assets->first();
                return [
                    'category_id' => $firstAsset->category_id,
                    'category_name' => $firstAsset->category?->name ?? 'Unknown',
                    'total_count' => $assets->sum('count'),
                    'asset_count' => $assets->count(),
                    'sub_summaries' => $assets->groupBy('sub_category_id')->map(fn($g) => [
                        'name' => $g->first()->subCategory?->name ?? 'General',
                        'count' => $g->count()
                    ])->values()
                ];
            })
            ->values()
            ->sortBy('category_name')
            ->values()
            ->toArray();
    }

    public function store(Request $request): RedirectResponse
    {
        $departmentId = $request->session()->get('selected_department_id');

        abort_unless($departmentId, 403);

        $entryType = $request->input('entry_type');
        
        $rules = [
            'entry_type' => ['required', 'in:individual,series,bundle'],
        ];
        
        if ($entryType === 'individual' || $entryType === 'series' || $entryType === 'bundle') {
            $rules['room_id'] = ['required', 'integer', 'exists:rooms,id'];
            $rules['category_id'] = ['required', 'integer', 'exists:categories,id'];
            $rules['sub_category_id'] = ['nullable', 'integer', 'exists:sub_categories,id'];
            $rules['note'] = ['nullable', 'string'];
            $rules['status'] = ['required', 'string', 'in:active,maintenance,damaged,retired,donated,lost'];
            $rules['is_shared'] = ['required', 'boolean'];
            $rules['shared_department_ids'] = ['array'];
            $rules['shared_department_ids.*'] = ['integer', 'exists:departments,id'];
            $rules['count'] = ['required', 'integer', 'min:1'];
            $rules['infos'] = ['array'];
            $rules['infos.*.key'] = ['nullable', 'string'];
            $rules['infos.*.value'] = ['nullable', 'string'];
            $rules['infos.*.image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
            $rules['is_parent'] = ['boolean'];
            
            // Component Validation for Bundles
            $rules['components'] = ['nullable', 'array'];
            $rules['components.*.category_id'] = ['required', 'integer', 'exists:categories,id'];
            $rules['components.*.sub_category_id'] = ['nullable', 'integer', 'exists:sub_categories,id'];
            $rules['components.*.status'] = ['required', 'string', 'in:active,maintenance,damaged'];
            $rules['components.*.note'] = ['nullable', 'string'];
            $rules['components.*.infos'] = ['nullable', 'array'];
            $rules['components.*.infos.*.key'] = ['required_with:components.*.infos.*.value', 'string'];
            $rules['components.*.infos.*.value'] = ['required_with:components.*.infos.*.key', 'string'];
        } else {
            // Legacy main+peers mode
            $rules['base_asset'] = ['required', 'array'];
            $rules['base_asset.room_id'] = ['required', 'integer', 'exists:rooms,id'];
            $rules['base_asset.category_id'] = ['required', 'integer', 'exists:categories,id'];
            $rules['base_asset.sub_category_id'] = ['nullable', 'integer', 'exists:sub_categories,id'];
            $rules['base_asset.note'] = ['nullable', 'string'];
            $rules['base_asset.status'] = ['required', 'string', 'in:active,maintenance,damaged,retired,donated,lost'];
            $rules['base_asset.is_shared'] = ['required', 'boolean'];
            $rules['base_asset.shared_department_ids'] = ['array'];
            $rules['peered_assets'] = ['array'];
            $rules['peered_assets.*.category_id'] = ['required', 'integer', 'exists:categories,id'];
            $rules['peered_assets.*.sub_category_id'] = ['nullable', 'integer', 'exists:sub_categories,id'];
            $rules['peered_assets.*.infos'] = ['array'];
            $rules['peered_assets.*.infos.*.key'] = ['nullable', 'string'];
            $rules['peered_assets.*.infos.*.value'] = ['nullable', 'string'];
            $rules['peered_assets.*.infos.*.image'] = ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'];
        }
        
        $data = $request->validate($rules);
        $assetCodeService = app(\App\Services\AssetCodeService::class);
        $room = Room::with('level.building')->find($data['room_id']);

        $asset = DB::transaction(function () use ($data, $departmentId, $request, $assetCodeService, $room) {
            $isSeries = $data['entry_type'] === 'series';
            $quantity = (int) ($data['count'] ?? 1);
            
            $category = Category::find($data['category_id']);
            $subCategory = $data['sub_category_id'] ? SubCategory::find($data['sub_category_id']) : null;
            
            // Sequential Series Numbering (Rule 3)
            $seriesNo = ($isSeries || ($data['is_parent'] ?? false)) 
                ? $assetCodeService->getNextSeriesNoInt($room) 
                : null;
            
            // New Bundle Serial Logic (Category-based)
            $bundleGroupNumber = $assetCodeService->getNextBundleNumber($data['category_id']);
            $categoryPrefix = $category->prefix ?? 'S00';
            $bundleSerial = $assetCodeService->generateBundleSerial($data['category_id'], $bundleGroupNumber);
            
            $storedImagePaths = [];
            $createdAsset = null;

            for ($i = 0; $i < $quantity; $i++) {
                $componentNo = $i + 1; // Numeric incremental only (Rule 1)
                
                // Create asset with null code first to get ID
                $createdAsset = Asset::create([
                    'department_id' => $departmentId,
                    'category_id' => $data['category_id'],
                    'room_id' => $data['room_id'],
                    'sub_category_id' => $data['sub_category_id'],
                    'note' => $data['note'] ?? null,
                    'count' => 1,
                    'asset_code' => null, // Placeholder
                    'series_no' => $seriesNo,
                    'sequence_number' => 0,
                    'component_no' => $componentNo,
                    'status' => $data['status'],
                    'is_shared' => $data['is_shared'],
                    'is_parent' => $data['is_parent'] ?? false,
                    'is_bundle_parent' => $data['is_parent'] ?? false,
                    'created_by_id' => $request->user()->id,
                    'bundle_serial' => $bundleSerial,
                    'bundle_group_number' => $bundleGroupNumber,
                    'category_prefix' => $categoryPrefix,
                    'full_serial' => null, // Placeholder
                ]);

                // Now generate real code with the ID
                $assetCode = $assetCodeService->generateAssetCode($createdAsset);
                $createdAsset->update([
                    'asset_code' => $assetCode,
                    'full_serial' => $assetCode
                ]);

                if ($data['is_shared'] && !empty($data['shared_department_ids'])) {
                    $createdAsset->sharedDepartments()->sync($data['shared_department_ids']);
                }

                foreach ($data['infos'] ?? [] as $index => $info) {
                    if (empty($info['key'])) continue;

                    $imagePath = $storedImagePaths[$index] ?? null;
                    if ($i === 0 && $request->hasFile("infos.{$index}.image")) {
                         $imagePath = $this->processAndStoreImage($request->file("infos.{$index}.image"));
                         $storedImagePaths[$index] = $imagePath;
                    }

                    AssetInfo::create([
                        'asset_id' => $createdAsset->id,
                        'key' => $info['key'],
                        'value' => $info['value'] ?? null,
                        'image' => $imagePath,
                    ]);
                }
            }

            if (!empty($data['infos'])) {
                $this->syncSpecTemplates($subCategory->category_id, $data['infos'] ?? []);
            }
            
              if ($data['is_parent'] ?? false) {
                  // Bundle Logic: Create components if present
                   if (!empty($data['components'])) {
                       foreach ($data['components'] as $compIndex => $comp) {
                          $componentNo = $assetCodeService->getNextComponentNo($createdAsset->id);
                          
                          // Create component with null code
                          $child = Asset::create([
                             'department_id' => $departmentId,
                             'category_id' => $comp['category_id'],
                             'room_id' => $data['room_id'], 
                             'sub_category_id' => $comp['sub_category_id'] ?? null,
                             'parent_id' => $createdAsset->id, 
                             'asset_code' => null,
                             'series_no' => $seriesNo,
                             'sequence_number' => 0,
                             'component_no' => $componentNo,
                             'status' => $comp['status'],
                             'note' => $comp['note'] ?? null,
                             'count' => 1,
                             'is_shared' => $data['is_shared'], 
                             'created_by_id' => $request->user()->id,
                             'bundle_serial' => $bundleSerial,
                             'bundle_group_number' => $bundleGroupNumber,
                             'category_prefix' => $categoryPrefix,
                             'full_serial' => null,
                         ]);

                         // Update with real code
                         $compCode = $assetCodeService->generateAssetCode($child);
                         $child->update([
                             'asset_code' => $compCode,
                             'full_serial' => $compCode
                         ]);

                         // Add Specs for Child
                         if (!empty($comp['infos'])) {
                             foreach ($comp['infos'] as $index => $childInfo) {
                                 if (empty($childInfo['key'])) continue;

                                 $childImagePath = null;
                                 if ($request->hasFile("components.{$compIndex}.infos.{$index}.image")) {
                                     $childImagePath = $this->processAndStoreImage($request->file("components.{$compIndex}.infos.{$index}.image"));
                                 }

                                 AssetInfo::create([
                                     'asset_id' => $child->id,
                                     'key' => $childInfo['key'],
                                     'value' => $childInfo['value'] ?? null,
                                     'image' => $childImagePath,
                                 ]);
                             }
                         }

                         // Inherit sharing
                         if ($data['is_shared'] && !empty($data['shared_department_ids'])) {
                             $child->sharedDepartments()->sync($data['shared_department_ids']);
                         }
                      }
                  }
            }
            
            return $createdAsset;
        });
        
        if ($asset) {
            $asset->load(['category', 'subCategory']);
        }

        if ($data['is_parent'] ?? false) {
             return redirect()->route('assets.create', ['room_id' => $data['room_id']])->with([
                'message' => 'System Bundle & Components created successfully.',
                'created_asset' => $asset
            ]);
        }

        // Fast Entry: stay on page with success message
        return redirect()->route('assets.create', ['room_id' => $data['room_id']])->with([
            'message' => 'Asset record successfully committed to enterprise inventory.',
        ]);
    }

    public function addComponents(Request $request, Asset $asset)
    {
        $departmentId = $request->session()->get('selected_department_id');
        $isOwner = $asset->department_id === $departmentId;
        abort_unless($isOwner, 403, 'Unauthorized access to this asset.');
        
        if (!$asset->is_parent) {
            return redirect()->route('assets.show', $asset->id);
        }

        $asset->load(['subCategory.category', 'room', 'children.subCategory']);

        return Inertia::render('Assets/AddComponents', [
            'asset' => $asset,
            'subCategories' => SubCategory::with('category')->get()
                ->map(fn($sc) => [
                    'id' => $sc->id, 
                    'name' => $sc->name,
                    'category_id' => $sc->category_id,
                    'label' => $sc->name // for SearchableSelect
                ]),
        ]);
    }

    public function storeComponents(Request $request, Asset $parent): JsonResponse
    {
        $departmentId = $request->session()->get('selected_department_id');
        $isOwner = $parent->department_id === $departmentId;
        abort_unless($isOwner, 403, 'Unauthorized access to this parent asset.');

        $data = $request->validate([
            'components' => ['required', 'array', 'min:1'],
            'components.*.category_id' => ['required', 'integer', 'exists:categories,id'],
            'components.*.sub_category_id' => ['nullable', 'integer', 'exists:sub_categories,id'],
            'components.*.status' => ['required', 'string', 'in:active,maintenance,damaged,retired,donated,lost'],
            'components.*.asset_code' => ['nullable', 'string', 'distinct'], 
            'components.*.note' => ['nullable', 'string'],
        ]);

        $assetCodeService = app(\App\Services\AssetCodeService::class);
        
        $createdComponents = DB::transaction(function () use ($data, $parent, $request, $assetCodeService) {
            $results = [];
            
            foreach ($data['components'] as $componentData) {
                // Generate code if not provided
                if (empty($componentData['asset_code'])) {
                    $dummy = new Asset([
                        'room_id' => $parent->room_id,
                        'category_id' => $componentData['category_id'],
                        'sub_category_id' => $componentData['sub_category_id'] ?? null,
                    ]);
                    $dummy->setRelation('room', $parent->room);
                    $dummy->setRelation('category', Category::find($componentData['category_id']));
                    if (!empty($componentData['sub_category_id'])) {
                        $dummy->setRelation('subCategory', SubCategory::with('category')->find($componentData['sub_category_id']));
                    }
                    $code = $assetCodeService->generateAssetCode($dummy);
                } else {
                    $code = $componentData['asset_code'];
                    if (Asset::where('asset_code', $code)->exists()) {
                         throw new \Exception("Asset ID {$code} already exists.");
                    }
                }

                $child = Asset::create([
                    'department_id' => $parent->department_id,
                    'room_id' => $parent->room_id, 
                    'category_id' => $componentData['category_id'],
                    'sub_category_id' => $componentData['sub_category_id'] ?? null,
                    'parent_id' => $parent->id,
                    'asset_code' => $code,
                    'sequence_number' => $assetCodeService->getNextGlobalCounter(),
                    'status' => $componentData['status'],
                    'note' => $componentData['note'] ?? null,
                    'count' => 1,
                    'is_shared' => $parent->is_shared, 
                    'created_by_id' => $request->user()->id,
                ]);
                
                $results[] = $child;
            }
            
             // Sync sharing if parent is shared
            if ($parent->is_shared) {
                $sharedIds = $parent->sharedDepartments->pluck('id');
                foreach ($results as $child) {
                    $child->sharedDepartments()->sync($sharedIds);
                }
            }

            return $results;
        });

        // Load relationships for response
        foreach ($createdComponents as $child) {
            $child->load(['category', 'subCategory']);
        }

        return response()->json([
            'message' => 'Components added successfully.',
            'components' => collect($createdComponents)->map(fn($child) => [
                'id' => $child->id,
                'asset_code' => $child->asset_code,
                'name' => ($child->category?->name ?? 'Unknown') . ($child->subCategory ? " - {$child->subCategory->name}" : ""),
                'status' => $child->status,
            ]),
        ]);
    }

    public function show(Request $request, Asset $asset): Response|RedirectResponse
    {
        try {
            $departmentId = $request->session()->get('selected_department_id');

            // 1. Department Session Verification
            if (!$departmentId) {
                return redirect()->route('dashboard')->with('error', 'Please select a department to proceed.');
            }

            // 2. Load and verify relationships
            $asset->load([
                'department',
                'room.level.building.location',
                'subCategory.category',
                'infos',
                'creator.roles',
                'sharedDepartments',
                'groupType'
            ]);

            // 3. Permission & Shared Access Verification
            $isOwner = $asset->department_id == $departmentId;
            $isShared = $asset->sharedDepartments->pluck('id')->contains($departmentId); 
            $isSuperAdmin = $request->user()?->hasRole('SuperAdmin') ?? false;

            if (!$isOwner && !$isShared && !$isSuperAdmin) {
                abort(403, 'Unauthorized access to this asset record across departments.');
            }

            // Hierarchy Loading
            if ($asset->is_parent) {
                $asset->load(['children.subCategory', 'children.department']);
                $asset->append('bundle_status');
            }
            if ($asset->parent_id) {
                $asset->load('parent.subCategory');
            }

            // 4. Room Asset Summary
            $roomAssetsSummary = $this->getRoomAssetsSummary($asset->room_id);

            // 5. Data Assembly
            return Inertia::render('Assets/Show', [
                'asset' => $this->formatAssetDetails($asset),
                'roomAssetsSummary' => $roomAssetsSummary,
                'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Asset View Error: " . $e->getMessage());
            
            return Inertia::render('Assets/Show', [
                'asset' => null,
                'errorMessage' => $e->getMessage(),
                'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
            ]);
        }
    }

    public function manageGroup(Request $request, Asset $asset): mixed
    {
        $user = $request->user();
        $departmentId = $request->session()->get('selected_department_id');
        
        $isSuperAdmin = $user->hasRole('SuperAdmin');
        $isOwner = $asset->department_id === $departmentId;

        if (!$isSuperAdmin && !$isOwner) {
            return redirect()->back()->with('error', 'Unauthorized access. This asset belongs to another department.');
        }

        $asset->load(['children.subCategory', 'groupType']);
        $groupTypes = AssetGroupType::all();

        return Inertia::render('Assets/GroupManagement', [
            'asset' => $this->formatAssetDetails($asset),
            'groupTypes' => $groupTypes,
        ]);
    }

    public function searchAssets(Request $request): JsonResponse
    {
        $user = $request->user();
        $departmentId = $request->session()->get('selected_department_id');
        $search = $request->input('search');

        $query = Asset::query();

        if (!$user->hasRole('SuperAdmin')) {
            $query->where('department_id', $departmentId);
        }

        $assets = $query->whereNull('parent_id')
            ->where('id', '!=', $request->input('exclude_id'))
            ->where(function($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('series_no', 'like', "%{$search}%")
                  ->orWhereHas('subCategory', fn($sq) => $sq->where('name', 'like', "%{$search}%"));
            })
            ->with(['subCategory', 'department'])
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'id' => $a->id,
                'asset_code' => $a->asset_code,
                'name' => $a->subCategory?->name,
                'department' => $a->department?->name,
                'label' => "{$a->asset_code} - " . ($a->subCategory?->name ?? 'Unknown') . " ({$a->department?->name})",
            ]);

        return response()->json($assets);
    }

    public function updateGroup(Request $request, Asset $asset): RedirectResponse
    {
        $user = $request->user();
        $departmentId = $request->session()->get('selected_department_id');
        
        $isSuperAdmin = $user->hasRole('SuperAdmin');
        $isOwner = $asset->department_id === $departmentId;

        if (!$isSuperAdmin && !$isOwner) {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $data = $request->validate([
            'group_name' => ['nullable', 'string', 'max:255'],
            'group_type_id' => ['nullable', 'integer', 'exists:asset_group_types,id'],
            'child_ids' => ['array'],
            'child_ids.*' => ['integer', 'exists:assets,id'],
        ]);

        DB::transaction(function () use ($data, $asset) {
            $asset->update([
                'group_name' => $data['group_name'],
                'group_type_id' => $data['group_type_id'],
                'is_parent' => !empty($data['child_ids']) || ($data['group_type_id'] !== null),
            ]);

            Asset::where('parent_id', $asset->id)
                ->whereNotIn('id', $data['child_ids'] ?? [])
                ->update(['parent_id' => null]);

            if (!empty($data['child_ids'])) {
                Asset::whereIn('id', $data['child_ids'])
                    ->update(['parent_id' => $asset->id]);
            }
        });

        return redirect()->route('assets.show', $asset->id)
            ->with('message', 'Asset grouping updated successfully.');
    }

    public function storeComponent(Request $request, Asset $asset): JsonResponse
    {
        $departmentId = $request->session()->get('selected_department_id');
        $user = $request->user();
        if (!$user->hasRole('SuperAdmin') && $asset->department_id !== $departmentId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'sub_category_id' => 'nullable|exists:sub_categories,id',
            'status' => 'required|string|in:active,maintenance,damaged',
            'note' => 'nullable|string',
            'infos' => 'nullable|array',
            'infos.*.key' => 'required|string',
            'infos.*.value' => 'required|string',
        ]);

        $category = Category::findOrFail($data['category_id']);
        $subCategory = !empty($data['sub_category_id']) ? SubCategory::findOrFail($data['sub_category_id']) : null;
        $room = $asset->room()->with('level.building')->first();
        $assetCodeService = app(\App\Services\AssetCodeService::class);

        $child = DB::transaction(function () use ($data, $asset, $departmentId, $user, $room, $category, $subCategory, $assetCodeService) {
            $componentNo = $assetCodeService->getNextComponentNo($asset->id);
            
            $dummy = new Asset([
                'room_id' => $asset->room_id,
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'series_no' => $asset->series_no,
                'component_no' => $componentNo,
                'parent_id' => $asset->id,
                'category_prefix' => $asset->category_prefix,
                'bundle_group_number' => $asset->bundle_group_number,
            ]);
            $dummy->setRelation('room', $room);
            $dummy->setRelation('category', $category);
            if ($subCategory) {
                $dummy->setRelation('subCategory', $subCategory);
            }
            
            $code = $assetCodeService->generateAssetCode($dummy);

            $newAsset = Asset::create([
                'department_id' => $departmentId,
                'room_id' => $asset->room_id,
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'note' => $data['note'],
                'count' => 1,
                'asset_code' => $code,
                'series_no' => $asset->series_no,
                'component_no' => $componentNo,
                'sequence_number' => $assetCodeService->getNextGlobalCounter(),
                'status' => $data['status'],
                'is_shared' => false,
                'parent_id' => $asset->id,
                'created_by_id' => $user->id,
                'bundle_serial' => $asset->bundle_serial,
                'bundle_group_number' => $asset->bundle_group_number,
                'category_prefix' => $asset->category_prefix,
                'full_serial' => $code,
            ]);

            if (!empty($data['infos'])) {
                foreach ($data['infos'] as $info) {
                    AssetInfo::create([
                        'asset_id' => $newAsset->id,
                        'key' => $info['key'],
                        'value' => $info['value'],
                    ]);
                }
            }

            return $newAsset;
        });

        return response()->json([
            'id' => $child->id,
            'asset_code' => $child->asset_code,
            'name' => ($category->name ?? 'Unknown') . ($subCategory ? " - {$subCategory->name}" : ""),
            'status' => $child->status,
            'infos' => $child->infos->map(fn($i) => [
                'key' => $i->key,
                'value' => $i->value
            ])
        ]);
    }

    public function attachComponent(Request $request, Asset $asset): RedirectResponse
    {
        $user = $request->user();
        $departmentId = $request->session()->get('selected_department_id');
        
        if (!$user->hasRole('SuperAdmin') && $asset->department_id !== $departmentId) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $request->validate([
            'child_id' => 'required|exists:assets,id'
        ]);

        $child = Asset::findOrFail($request->child_id);
        $child->update(['parent_id' => $asset->id]);
        
        $asset->update(['is_parent' => true]);

        return redirect()->back()->with('message', 'Component linked successfully.');
    }

    public function detachComponent(Request $request, Asset $asset): RedirectResponse
    {
        $user = $request->user();
        $departmentId = $request->session()->get('selected_department_id');
        
        if (!$user->hasRole('SuperAdmin') && $asset->department_id !== $departmentId) {
            return redirect()->back()->with('error', 'Unauthorized.');
        }

        $request->validate([
            'child_id' => 'required|exists:assets,id'
        ]);

        $child = Asset::where('id', $request->child_id)
            ->where('parent_id', $asset->id)
            ->firstOrFail();
            
        $child->update(['parent_id' => null]);

        return redirect()->back()->with('message', 'Component removed from system.');
    }

    /**
     * Format asset details for the Show view.
     */
    private function formatAssetDetails(Asset $asset): array
    {
        $room = $asset->room;
        $level = $room?->level;
        $building = $level?->building;
        $location = $building?->location;

        return [
            'id' => $asset->id,
            'asset_code' => $asset->asset_code ?? 'NO-CODE',
            'room_id' => $asset->room_id,
            'category_id' => $asset->category_id,
            'sub_category_id' => $asset->sub_category_id,
            'note' => $asset->note ?? 'No description provided.',
            'count' => $asset->count ?? 1,
            'status' => $asset->status ?? 'active',
            'is_shared' => (bool) $asset->is_shared,
            'peered_asset_id' => $asset->peered_asset_id,
            'short_code' => $asset->short_code,
            'bundle_serial' => $asset->bundle_serial,
            'full_serial' => $asset->full_serial,
            'created_at' => $asset->created_at ? $asset->created_at->format('d M Y — h:i A') : 'System Date',
            'updated_at' => $asset->updated_at ? $asset->updated_at->format('d M Y — h:i A') : 'System Date',
            'creator' => $asset->creator ? [
                'name' => $asset->creator->name,
                'image_url' => $asset->creator->image_url,
                'id' => $asset->creator->id,
                'role' => $asset->creator->roles->pluck('name')->first() ?? 'Authorized Personnel',
            ] : [
                'name' => 'System / Archived User',
                'image_url' => null,
                'id' => 0,
                'role' => 'Logistics Engine'
            ],
            'department_info' => [
                'id' => $asset->department_id,
                'name' => $asset->department?->name ?? 'Institutional',
                'code' => $asset->department?->code ?? 'N/A',
            ],
            'category' => $asset->category?->name ?? 'General',
            'subCategory' => $asset->subCategory?->name ?? 'General',
            'owner_department' => $asset->department?->name ?? 'Institutional',
            'room' => [
                'id' => $room?->id,
                'name' => $room?->name ?? 'Not Assigned',
                'code' => $room?->code ?? '-',
                'level' => $level?->name ?? 'Level 0',
                'building' => $building?->name ?? 'Main Campus',
                'location' => $location?->name ?? 'HQ',
            ],
            'infos' => $asset->infos->map(function ($info) {
                return [
                    'id' => $info->id,
                    'key' => $info->key,
                    'value' => $info->value,
                    'image_url' => $info->image_url,
                ];
            }),
            'image_url' => $asset->image_url,
            'shared_departments' => $asset->sharedDepartments->pluck('name')->values(),
            'shared_department_ids' => $asset->sharedDepartments->pluck('id')->values(),
            'movements' => $asset->movements()
                ->with(['fromRoom', 'toRoom', 'fromDepartment', 'toDepartment', 'user'])
                ->orderByDesc('created_at')
                ->get()
                ->values(),
            // Hierarchy Data
            'is_parent' => (bool)$asset->is_parent,
            'parent_id' => $asset->parent_id,
            'bundle_status' => $asset->bundle_status,
            'group_name' => $asset->group_name,
            'group_type' => $asset->groupType ? [
                'id' => $asset->groupType->id,
                'name' => $asset->groupType->name,
                'icon' => $asset->groupType->icon,
            ] : null,
            'parent' => $asset->parent ? [
                'id' => $asset->parent->id,
                'name' => ($asset->parent->category?->name ?? 'System') . ($asset->parent->subCategory ? " - {$asset->parent->subCategory->name}" : ""),
                'code' => $asset->parent->asset_code,
                'bundle_serial' => $asset->parent->bundle_serial,
            ] : null,
            'children' => $asset->children ? $asset->children->map(fn($c) => [
                'id' => $c->id,
                'name' => ($c->category?->name ?? 'Unknown Component') . ($c->subCategory ? " - {$c->subCategory->name}" : ""),
                'asset_code' => $c->asset_code,
                'bundle_serial' => $c->bundle_serial,
                'status' => $c->status,
                'image_url' => $c->image_url,
                'created_at_human' => $c->created_at ? $c->created_at->diffForHumans() : 'Date Unknown',
                'infos' => $c->infos->map(fn($i) => [
                    'key' => $i->key,
                    'value' => $i->value
                ])
            ])->values() : [],
        ];
    }


    public function edit(Asset $asset, Request $request): Response
    {
        $asset->load(['infos', 'category', 'subCategory', 'room.level.building.location', 'creator', 'children.category', 'children.subCategory']);
        
        return Inertia::render('Assets/Edit', [
            'asset' => $this->formatAssetDetails($asset),
            'rooms' => $this->getRoomsForSelection(),
            'classifications' => $this->getClassificationsForSelection(),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
            'subCategories' => SubCategory::orderBy('name')->get(['id', 'name', 'category_id']),
            'departments' => \App\Models\Department::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        try {
            $departmentId = $request->session()->get('selected_department_id');
            $isOwner = $asset->department_id === $departmentId;
            $isSuperAdmin = $request->user()->hasRole('SuperAdmin');
            abort_unless($isOwner || $isSuperAdmin, 403);

            $data = $request->validate([
                'room_id' => ['required', 'integer', 'exists:rooms,id'],
                'category_id' => ['required', 'integer', 'exists:categories,id'],
                'sub_category_id' => ['nullable', 'integer', 'exists:sub_categories,id'],
                'note' => ['nullable', 'string'],
                'count' => ['required', 'integer', 'min:1'],
                'status' => ['required', 'string', 'in:active,maintenance,damaged,retired,donated,lost'],
                'is_shared' => ['required', 'boolean'],
                'shared_department_ids' => ['array'],
                'shared_department_ids.*' => ['integer', 'exists:departments,id'],
                'infos' => ['array'],
                'infos.*.id' => ['nullable', 'integer'],
                'infos.*.key' => ['required', 'string'],
                'infos.*.value' => ['nullable', 'string'],
                'infos.*.image' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            ]);

            // Security: Remove visibility fields if user lacks permission
            if ($request->user()->cannot('manage-asset-visibility')) {
                unset($data['is_shared']);
                unset($data['shared_department_ids']);
            }

            $oldRoomId = $asset->room_id;
            $oldDepartmentId = $asset->department_id;

            DB::transaction(function () use ($data, $asset, $request, $oldRoomId, $oldDepartmentId) {
                $asset->update([
                    'room_id' => $data['room_id'],
                    'category_id' => $data['category_id'],
                    'sub_category_id' => $data['sub_category_id'],
                    'note' => $data['note'] ?? null,
                    'count' => $data['count'] ?? 1,
                    'status' => $data['status'],
                    'is_shared' => $data['is_shared'] ?? $asset->is_shared,
                ]);

                if (isset($data['is_shared'])) {
                    if ($data['is_shared']) {
                        $asset->sharedDepartments()->sync($data['shared_department_ids'] ?? []);
                    } else {
                        $asset->sharedDepartments()->detach();
                    }
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
                
                // Process incoming infos (update or create)
                foreach ($incoming as $index => $infoData) {
                    $imagePath = null;
                    // Handle image upload if present
                    if ($request->hasFile("infos.{$index}.image")) {
                        $imagePath = $this->processAndStoreImage($request->file("infos.{$index}.image"));
                    }

                    if (!empty($infoData['id'])) {
                        // Update existing info
                        $existingInfo = AssetInfo::find($infoData['id']);
                        if ($existingInfo) {
                            $updateData = [
                                'key' => $infoData['key'],
                                'value' => $infoData['value'] ?? null,
                            ];
                            if ($imagePath) {
                                // Delete old image if verified local
                                if ($existingInfo->image && !str_starts_with($existingInfo->image, 'http')) {
                                     Storage::disk('public')->delete($existingInfo->image);
                                }
                                $updateData['image'] = $imagePath;
                            }
                            $existingInfo->update($updateData);
                        }
                    } else {
                        // Create new info
                         AssetInfo::create([
                            'asset_id' => $asset->id,
                            'key' => $infoData['key'],
                            'value' => $infoData['value'] ?? null,
                            'image' => $imagePath,
                        ]);
                    }
                }
            });

            return redirect()
                ->route('assets.show', $asset->id)
                ->with('success', 'Asset updated successfully');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Asset Update Failed: " . $e->getMessage());

            return back()->with('error', 'Update failed: ' . $e->getMessage());
        }
    }


    public function destroy(Request $request, Asset $asset): JsonResponse
    {
        $departmentId = $request->session()->get('selected_department_id');
        $isOwner = $asset->department_id === $departmentId;
        $isSuperAdmin = $request->user()->hasRole('SuperAdmin');
        abort_unless($isOwner || $isSuperAdmin, 403);

        // Safety Logic: Block deletion if it has linked records
        
        // 1. Check for child components - Recursively delete them (Rule 7 Updated: Auto-cleanup)
        if ($asset->is_parent) {
            $asset->children()->delete(); // Soft delete all children
        }

        // 2. Check for movement/audit history (Rule 2 & 7: Components/Assets with history are protected)
        // Allow SuperAdmin to force delete even with history
        if ($asset->movements()->exists() && !$isSuperAdmin) {
            return response()->json([
                'error' => 'Cannot delete — asset has historical audit/movement logs.'
            ], 422);
        }

        // 4. Perform Soft Delete (Rule 3)
        $asset->delete();

        return response()->json([
            'message' => 'Asset record successfully archived from inventory.'
        ]);
    }

    public function transfer(Request $request, Asset $asset): JsonResponse
    {
        // Only SuperAdmins can transfer assets between departments
        /** @var \App\Models\User $authUser */
        $authUser = $request->user();
        abort_unless($authUser->hasRole('SuperAdmin'), 403);

        $data = $request->validate([
            'department_id' => ['required', 'integer', 'exists:departments,id'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $oldDepartmentId = $asset->department_id;

        if ($oldDepartmentId == $data['department_id']) {
            return response()->json(['message' => 'Asset is already in this department.'], 400);
        }

        DB::transaction(function () use ($asset, $data, $oldDepartmentId, $request) {
            $asset->update([
                'department_id' => $data['department_id']
            ]);

            // Record movement
            \App\Models\AssetMovement::create([
                'asset_id' => $asset->id,
                'from_department_id' => $oldDepartmentId,
                'to_department_id' => $data['department_id'],
                'user_id' => $request->user()->id,
                'reason' => $data['reason'] ?? 'Department transfer via Drag & Drop',
            ]);
        });

        return response()->json([
            'message' => 'Asset transferred successfully.',
            'asset_id' => $asset->id,
            'new_department_id' => $data['department_id']
        ]);
    }

    public function updateStatus(Request $request, Asset $asset): RedirectResponse
    {
        $departmentId = $request->session()->get('selected_department_id');
        $isOwner = $asset->department_id === $departmentId;
        $isSuperAdmin = $request->user()->hasRole('SuperAdmin');
        abort_unless($isOwner || $isSuperAdmin, 403);

        /** @var \App\Models\User $user */
        $user = $request->user();
        
        // Data entry cannot change status after initial save
        if ($user->hasRole('Data Entry') && !$user->hasRole('SuperAdmin') && !$user->hasRole('Admin')) {
            abort(403, 'Insufficient permissions to modify status.');
        }

        $data = $request->validate([
            'status' => ['required', 'string', 'in:active,maintenance,damaged,retired,donated,lost'],
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $asset->update(['status' => $data['status']]);

        // Record movement/audit logic here if needed

        return back()->with('message', 'Asset status updated successfully.');
    }

    public function updateSharing(Request $request, Asset $asset): RedirectResponse
    {
        $departmentId = $request->session()->get('selected_department_id');
        $isOwner = $asset->department_id === $departmentId;
        $isSuperAdmin = $request->user()->hasRole('SuperAdmin');
        abort_unless($isOwner || $isSuperAdmin, 403);

        /** @var \App\Models\User $user */
        $user = $request->user();
        abort_unless($user->hasRole('SuperAdmin') || $user->hasRole('Admin'), 403);

        $data = $request->validate([
            'is_shared' => ['required', 'boolean'],
            'shared_department_ids' => ['array'],
            'shared_department_ids.*' => ['integer', 'exists:departments,id'],
        ]);

        DB::transaction(function () use ($asset, $data) {
            $asset->update(['is_shared' => $data['is_shared']]);
            
            if ($data['is_shared']) {
                $asset->sharedDepartments()->sync($data['shared_department_ids'] ?? []);
            } else {
                $asset->sharedDepartments()->detach();
            }
        });

        return back()->with('message', 'Sharing settings updated.');
    }
    private function syncSpecTemplates(int $categoryId, array $infos): void
    {
        $newKeysAdded = false;
        foreach ($infos as $info) {
            if (!empty($info['key'])) {
                $created = SpecTemplate::firstOrCreate([
                    'category_id' => $categoryId,
                    'key_name' => $info['key']
                ]);
                
                if ($created->wasRecentlyCreated) {
                    $newKeysAdded = true;
                }
            }
        }

        if ($newKeysAdded) {
            Cache::forget("category_{$categoryId}_spec_templates");
        }
    }

    /**
     * Process and optimize uploaded image for storage.
     */
    private function processAndStoreImage($file): string
    {
        $imageOptimizer = app(\App\Services\ImageOptimizationService::class);
        $result = $imageOptimizer->processImage($file, 'asset_infos', false);
        return $result['full'];
    }
}
