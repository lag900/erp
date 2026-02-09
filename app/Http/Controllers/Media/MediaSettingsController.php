<?php

namespace App\Http\Controllers\Media;

use App\Http\Controllers\Controller;
use App\Models\MediaSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MediaSettingsController extends Controller
{
    public function edit()
    {
        $settings = MediaSetting::firstOrCreate([], [
            'fb_enabled' => false,
            'fb_auto_publish' => false,
            'ig_enabled' => false,
        ]);

        return Inertia::render('Media/Settings/Edit', [
            'settings' => $settings
        ]);
    }

    public function update(Request $request)
    {
        $settings = MediaSetting::first();
        
        $validated = $request->validate([
            'fb_page_url' => 'nullable|url',
            'fb_page_id' => 'nullable|string',
            'fb_access_token' => 'nullable|string',
            'fb_enabled' => 'boolean',
            'fb_auto_publish' => 'boolean',
            'ig_page_url' => 'nullable|url',
            'ig_enabled' => 'boolean',
            'ig_embed_token' => 'nullable|string',
        ]);

        $settings->update($validated);

        return redirect()->back()->with('success', 'Media settings updated successfully.');
    }
}
