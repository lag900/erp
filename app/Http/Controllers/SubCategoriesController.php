<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSubCategoryRequest;
use App\Http\Requests\UpdateSubCategoryRequest;
use App\Models\Category;
use App\Models\SubCategory;
use App\Services\FileService;
use App\Services\CodeGeneratorService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SubCategoriesController extends Controller
{
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function index(Request $request): Response
    {
        $subCategories = SubCategory::with('category')
            ->orderBy('name')
            ->get()
            ->map(fn (SubCategory $subCategory) => [
                'id' => $subCategory->id,
                'name' => $subCategory->name,
                'name_ar' => $subCategory->name_ar,
                'code' => $subCategory->code,
                'category' => $subCategory->category?->name,
                'image_url' => $subCategory->image_url,
            ]);

        return Inertia::render('SubCategories/Index', [
            'subCategories' => $subCategories,
        ]);
    }

    public function create(): Response
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('SubCategories/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(StoreSubCategoryRequest $request)
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request) {
                $data = $request->validated();

                // Auto Generate or Safeguard provided code
                if (empty($data['code'])) {
                    $data['code'] = CodeGeneratorService::generateModelCode($data['name'], SubCategory::class);
                } else {
                    $data['code'] = CodeGeneratorService::makeUnique($data['code'], SubCategory::class);
                }

                if ($request->hasFile('image')) {
                    $data['image'] = $this->fileService->updateFile($request->file('image'), 'subcategories');
                }

                SubCategory::create($data);

                return redirect()->route('subcategories.index')->with('success', 'Sub-category created successfully.');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to create subcategory: ' . $e->getMessage());
            return back()->with('error', 'Could not create subcategory. The system encountered a conflict or error. Please try again.');
        }
    }

    public function edit(SubCategory $subCategory): Response
    {
        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('SubCategories/Edit', [
            'subCategory' => [
                'id' => $subCategory->id,
                'name' => $subCategory->name,
                'code' => $subCategory->code,
                'category_id' => $subCategory->category_id,
                'image_url' => $subCategory->image_url,
            ],
            'categories' => $categories,
        ]);
    }

    public function update(UpdateSubCategoryRequest $request, SubCategory $subCategory)
    {
        try {
            return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $subCategory) {
                $data = $request->validated();
                unset($data['image']);

                // Ensure unique code if provided and changed
                if (!empty($data['code']) && $data['code'] !== $subCategory->code) {
                    $data['code'] = CodeGeneratorService::makeUnique($data['code'], SubCategory::class);
                }

                if ($request->hasFile('image')) {
                    $data['image'] = $this->fileService->updateFile($request->file('image'), 'subcategories', $subCategory->image);
                }

                $subCategory->update($data);

                return redirect()->route('subcategories.index')->with('success', 'Sub-category updated successfully.');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Failed to update subcategory: ' . $e->getMessage());
            return back()->with('error', 'Could not update subcategory. A conflict or error occurred.');
        }
    }

    public function destroy(SubCategory $subCategory, Request $request)
    {
        if ($subCategory->image) {
            $this->fileService->deleteFile($subCategory->image);
        }

        $subCategory->delete();

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Sub-category deleted successfully.']);
        }

        return redirect()->route('subcategories.index')->with('success', 'Sub-category deleted successfully.');
    }

    public function getByCategory(Request $request): \Illuminate\Http\JsonResponse
    {
        $categoryId = $request->query('category_id');
        if (!$categoryId) return response()->json([]);

        $items = SubCategory::where('category_id', $categoryId)
            ->orderBy('name')
            ->get(['id', 'name', 'name_ar']);

        return response()->json($items);
    }
}
