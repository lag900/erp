<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SubCategoriesController extends Controller
{
    public function index(): Response
    {
        $subCategories = SubCategory::with('category')
            ->orderBy('name')
            ->get()
            ->map(function (SubCategory $subCategory) {
                return [
                    'id' => $subCategory->id,
                    'name' => $subCategory->name,
                    'category' => $subCategory->category?->name,
                    'image_url' => $subCategory->image_url,
                ];
            });

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

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('subcategories', 'public');
        }

        SubCategory::create($data);

        return redirect()->route('subcategories.index');
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
                'category_id' => $subCategory->category_id,
                'image_url' => $subCategory->image_url,
            ],
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, SubCategory $subCategory): RedirectResponse
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($subCategory->image) {
                Storage::disk('public')->delete($subCategory->image);
            }
            $data['image'] = $request->file('image')->store('subcategories', 'public');
        }

        $subCategory->update($data);

        return redirect()->route('subcategories.index');
    }

    public function destroy(SubCategory $subCategory): RedirectResponse
    {
        // Delete image if exists
        if ($subCategory->image) {
            Storage::disk('public')->delete($subCategory->image);
        }

        $subCategory->delete();

        return redirect()->route('subcategories.index');
    }
}
