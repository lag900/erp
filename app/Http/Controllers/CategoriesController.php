<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;
use App\Models\Department;
use App\Services\CategoryService;
use App\Services\FileService;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class CategoriesController extends Controller
{
    protected $fileService;
    protected $categoryService;

    public function __construct(FileService $fileService, CategoryService $categoryService)
    {
        $this->fileService = $fileService;
        $this->categoryService = $categoryService;
    }

    public function index(Request $request): Response
    {
        $departmentId = $request->session()->get('selected_department_id');
        $authUser = $request->user();
        $isSuperAdmin = $authUser->hasRole('SuperAdmin');

        // Optimized with Eager Loading (remove N+1)
        $categories = Category::with('departments')
            ->when(!$isSuperAdmin && $departmentId, function ($query) use ($departmentId) {
                $query->whereHas('departments', fn($q) => $q->where('departments.id', $departmentId));
            })
            ->orderBy('name')
            ->get()
            ->map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'name_ar' => $category->name_ar,
                'code' => $category->code,
                'image_url' => $category->image_url,
                'department_names' => $category->departments->pluck('name')->join(', '),
                'department_ids' => $category->departments->pluck('id'),
            ]);

        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'departments' => $departments,
        ]);
    }

    public function create(): Response
    {
        $departments = Department::orderBy('name')->get(['id', 'name']);
        return Inertia::render('Categories/Create', [
            'departments' => $departments
        ]);
    }

    public function edit(Category $category): Response
    {
        $category->load('departments');
        $departments = Department::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'name_ar' => $category->name_ar,
                'code' => $category->code,
                'image_url' => $category->image_url,
                'department_ids' => $category->departments->pluck('id'),
            ],
            'departments' => $departments
        ]);
    }

    public function store(StoreCategoryRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();
            
            // Auto Generate Code if not provided or just generate it (based on logic)
            // Assuming generating is always desired as per currently existing code
            try {
                $data['code'] = $this->categoryService->generateUniqueCode($data['name']);
            } catch (\Throwable $e) {
                Log::error('Failed to generate category code: ' . $e->getMessage());
                // Fallback or accept that it might be null if database allows, or generate a simple random one
                $data['code'] = 'CAT-' . uniqid();
            }

            // Handle Image safely
            if ($request->hasFile('image')) {
                try {
                    $data['image'] = $this->fileService->updateFile($request->file('image'), 'categories');
                } catch (\Throwable $e) {
                    Log::error('Image upload failed for category: ' . $e->getMessage());
                    // Continue without image, do not crash
                    $data['image'] = null;
                }
            }

            $category = Category::create($data);
            
            // Sync departments if provided
            if (!empty($data['department_ids'])) {
                $category->departments()->sync($data['department_ids']);
            }

            DB::commit();

            return redirect()->route('categories.index')->with('success', 'Category created successfully.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Creating category failed: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            
            return back()->with('error', 'Internal system malfunction. Please try again shortly. Error: ' . substr($e->getMessage(), 0, 50));
        }
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        try {
            return DB::transaction(function () use ($request, $category) {
                $data = $request->validated();
                unset($data['image']);

                // Ensure unique code if provided and changed
                if (!empty($data['code']) && $data['code'] !== $category->code) {
                    $data['code'] = CodeGeneratorService::makeUnique($data['code'], Category::class);
                }

                // Handle Image with Automatic Old Image Deletion
                if ($request->hasFile('image')) {
                    $data['image'] = $this->fileService->updateFile($request->file('image'), 'categories', $category->image);
                }

                $category->update($data);
                $category->departments()->sync($data['department_ids']);

                return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Failed to update category: ' . $e->getMessage());
            return back()->with('error', 'Could not update category due to a system error.');
        }
    }

    public function destroy(Category $category, Request $request)
    {
        // Delete Image from Storage
        if ($category->image) {
            $this->fileService->deleteFile($category->image);
        }

        $category->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }

    public function getSpecTemplates(Category $category): \Illuminate\Http\JsonResponse
    {
        $templates = Cache::remember(
            "category_{$category->id}_spec_templates",
            now()->addDay(),
            fn() => $category->specTemplates()->orderBy('key_name')->pluck('key_name')
        );

        return response()->json($templates);
    }

    public function renameSpecTemplate(Request $request, Category $category)
    {
        $request->validate([
            'old_key' => 'required|string',
            'new_key' => 'required|string|max:255',
        ]);

        $oldName = $request->old_key;
        $newName = $request->new_key;

        // Prevent collisions
        $exists = $category->specTemplates()->where('key_name', $newName)->exists();
        if ($exists) {
            return response()->json(['error' => 'A template with this name already exists'], 422);
        }

        try {
            DB::beginTransaction();

            // 1. Update the template record
            $template = $category->specTemplates()->where('key_name', $oldName)->first();
            if ($template instanceof \App\Models\SpecTemplate) {
                $template->update(['key_name' => $newName]);
            } else {
                // If template record doesn't exist but we're trying to rename it, 
                // it might be a legacy key or just missing from template table.
                // We create it to maintain consistency.
                $category->specTemplates()->create(['key_name' => $newName]);
            }

            // 2. Cascade rename to all assets in this category
            $assetIds = \App\Models\Asset::where('category_id', $category->id)->pluck('id');
            \App\Models\AssetInfo::whereIn('asset_id', $assetIds)
                ->where('key', $oldName)
                ->update(['key' => $newName]);

            // 3. Clear Cache
            Cache::forget("category_{$category->id}_spec_templates");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Template renamed and assets updated.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Template Rename Failure: ' . $e->getMessage());
            return response()->json(['error' => 'Critical data update failure.'], 500);
        }
    }
}
