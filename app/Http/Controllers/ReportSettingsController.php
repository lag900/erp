<?php

namespace App\Http\Controllers;

use App\Models\ReportSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ReportSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified', 'permission:report-access']);
    }

    public function edit()
    {
        $user = Auth::user();
        $department = $user->department;

        if (!$department) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be assigned to a department to configure report settings.');
        }

        $settings = ReportSetting::forDepartment($department->id);

        return Inertia::render('Reports/Settings', [
            'settings' => $settings,
            'department' => $department,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $department = $user->department;

        if (!$department) {
            return redirect()->route('dashboard')
                ->with('error', 'You must be assigned to a department to configure report settings.');
        }

        $validated = $request->validate([
            'right_logo_file' => 'nullable|image|max:2048',
            'center_logo_file' => 'nullable|image|max:2048',
            'left_logo_file' => 'nullable|image|max:2048',
            'header_line_1' => 'nullable|string|max:255',
            'header_line_2' => 'nullable|string|max:255',
            'header_line_3' => 'nullable|string|max:255',
            'header_text_color' => 'nullable|string|max:7',
            'body_text_color' => 'nullable|string|max:7',
            'custom_header_title' => 'nullable|string|max:255',
            'signatures' => 'nullable|array',
            'signatures.*.title' => 'required|string|max:255',
            'signatures.*.name' => 'nullable|string|max:255',
            'show_stamp' => 'boolean',
            'stamp_label' => 'nullable|string|max:255',
            'remove_right_logo' => 'boolean',
            'remove_center_logo' => 'boolean',
            'remove_left_logo' => 'boolean',
        ]);

        $settings = ReportSetting::forDepartment($department->id);

        // Handle logo uploads
        if ($request->hasFile('right_logo_file')) {
            if ($settings->right_logo_path) {
                Storage::disk('public')->delete($settings->right_logo_path);
            }
            $settings->right_logo_path = $request->file('right_logo_file')
                ->store('logos/headers', 'public');
        }

        if ($request->hasFile('center_logo_file')) {
            if ($settings->center_logo_path) {
                Storage::disk('public')->delete($settings->center_logo_path);
            }
            $settings->center_logo_path = $request->file('center_logo_file')
                ->store('logos/headers', 'public');
        }

        if ($request->hasFile('left_logo_file')) {
            if ($settings->left_logo_path) {
                Storage::disk('public')->delete($settings->left_logo_path);
            }
            $settings->left_logo_path = $request->file('left_logo_file')
                ->store('logos/headers', 'public');
        }

        // Handle logo removals
        if ($request->boolean('remove_right_logo') && $settings->right_logo_path) {
            Storage::disk('public')->delete($settings->right_logo_path);
            $settings->right_logo_path = null;
        }

        if ($request->boolean('remove_center_logo') && $settings->center_logo_path) {
            Storage::disk('public')->delete($settings->center_logo_path);
            $settings->center_logo_path = null;
        }

        if ($request->boolean('remove_left_logo') && $settings->left_logo_path) {
            Storage::disk('public')->delete($settings->left_logo_path);
            $settings->left_logo_path = null;
        }

        // Update text settings
        $settings->header_line_1 = $request->input('header_line_1');
        $settings->header_line_2 = $request->input('header_line_2');
        $settings->header_line_3 = $request->input('header_line_3');
        $settings->header_text_color = $request->input('header_text_color', '#000000');
        $settings->body_text_color = $request->input('body_text_color', '#1a1a1a');
        $settings->custom_header_title = $request->input('custom_header_title');
        
        // Update signatures
        $settings->signatures = $request->input('signatures', []);
        
        // Update stamp settings
        $settings->show_stamp = $request->boolean('show_stamp');
        $settings->stamp_label = $request->input('stamp_label');

        $settings->save();

        return redirect()->route('reports.settings')
            ->with('success', 'Report settings updated successfully.');
    }
}
