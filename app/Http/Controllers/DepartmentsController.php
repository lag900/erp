<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Laravel\Facades\Image;

class DepartmentsController extends Controller
{
    public function index(): Response
    {
        $departments = Department::orderBy('display_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Departments/Index', [
            'departments' => $departments,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Departments/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'arabic_name' => ['nullable', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:departments,code'],
            'description' => ['nullable', 'string'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'director_title' => ['nullable', 'string', 'max:255'],
            'director_image' => ['nullable', 'image', 'max:5120'],
            'display_order' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:active,inactive,hidden'],
            'type' => ['required', 'string', 'in:faculty,department'],
        ]);

        if ($request->hasFile('director_image')) {
            $data['director_image'] = $this->processAndStoreImage($request->file('director_image'));
        }

        Department::create($data);

        return redirect()->route('departments.index')->with('success', 'Department created successfully.');
    }

    public function edit(Department $department): Response
    {
        return Inertia::render('Departments/Edit', [
            'department' => $department,
        ]);
    }

    public function update(Request $request, Department $department): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'arabic_name' => ['nullable', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                'unique:departments,code,' . $department->id,
            ],
            'description' => ['nullable', 'string'],
            'director_name' => ['nullable', 'string', 'max:255'],
            'director_title' => ['nullable', 'string', 'max:255'],
            'director_image' => ['nullable', 'image', 'max:5120'],
            'display_order' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:active,inactive,hidden'],
            'type' => ['required', 'string', 'in:faculty,department'],
        ]);

        if ($request->hasFile('director_image')) {
            if ($department->director_image) {
                Storage::disk('public')->delete($department->director_image);
            }
            $data['director_image'] = $this->processAndStoreImage($request->file('director_image'));
        }

        $department->update($data);

        return redirect()->route('departments.index')->with('success', 'Department updated successfully.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->director_image) {
            Storage::disk('public')->delete($department->director_image);
        }
        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted successfully.');
    }

    private function processAndStoreImage($file): string
    {
        $filename = 'dept_' . time() . '_' . uniqid() . '.webp';
        $path = 'departments/' . $filename;

        // Image Optimization - Convert to WebP, resize if needed, and compress
        $img = Image::read($file);
        
        // Resize to a maximum width of 1200px while maintaining aspect ratio
        if ($img->width() > 1200) {
            $img->scale(width: 1200);
        }
        
        $encoded = $img->toWebp(80);
        
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
