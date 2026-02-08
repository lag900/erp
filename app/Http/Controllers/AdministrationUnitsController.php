<?php

namespace App\Http\Controllers;

use App\Models\AdministrationUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Intervention\Image\Laravel\Facades\Image;

class AdministrationUnitsController extends Controller
{
    public function index(): Response
    {
        $units = AdministrationUnit::orderBy('display_order')
            ->orderBy('title')
            ->get();

        return Inertia::render('AdministrationUnits/Index', [
            'units' => $units,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('AdministrationUnits/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'director_name' => ['required', 'string', 'max:255'],
            'director_title' => ['required', 'string', 'max:255'],
            'director_image' => ['nullable', 'image', 'max:5120'], // 5MB
            'access_password' => ['nullable', 'string', 'min:4'],
            'display_order' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        if ($request->hasFile('director_image')) {
            $data['director_image'] = $this->processAndStoreImage($request->file('director_image'));
        }

        AdministrationUnit::create($data);

        return redirect()->route('administration.index')->with('success', 'Administration unit created successfully.');
    }

    public function edit(AdministrationUnit $unit): Response
    {
        return Inertia::render('AdministrationUnits/Edit', [
            'unit' => $unit,
        ]);
    }

    public function update(Request $request, AdministrationUnit $unit): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'director_name' => ['required', 'string', 'max:255'],
            'director_title' => ['required', 'string', 'max:255'],
            'director_image' => ['nullable', 'image', 'max:5120'],
            'access_password' => ['nullable', 'string', 'min:4'],
            'display_order' => ['required', 'integer'],
            'status' => ['required', 'string', 'in:active,inactive'],
        ]);

        unset($data['director_image']);

        if ($request->hasFile('director_image')) {
            // Delete old image if exists
            if ($unit->director_image) {
                Storage::disk('public')->delete($unit->director_image);
            }
            $data['director_image'] = $this->processAndStoreImage($request->file('director_image'));
        }

        // Only update password if provided
        if (empty($data['access_password'])) {
            unset($data['access_password']);
        }

        $unit->update($data);

        return redirect()->route('administration.index')->with('success', 'Administration unit updated successfully.');
    }

    public function destroy(AdministrationUnit $unit): RedirectResponse
    {
        if ($unit->director_image) {
            Storage::disk('public')->delete($unit->director_image);
        }
        $unit->delete();

        return redirect()->route('administration.index')->with('success', 'Administration unit deleted successfully.');
    }

    private function processAndStoreImage($file): string
    {
        $filename = 'admin_' . time() . '_' . uniqid() . '.jpg';
        $path = 'administration/' . $filename;

        // Optimize using Intervention Image
        $img = Image::read($file);
        $encoded = $img->toJpeg(80);
        
        Storage::disk('public')->put($path, (string) $encoded);

        return $path;
    }
}
