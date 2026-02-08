<?php

namespace App\Http\Controllers;

use App\Models\PermissionGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PermissionGroupsController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permission_groups,name'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
        ]);

        PermissionGroup::create($data);

        return redirect()->back()->with('success', 'Security module initialized.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PermissionGroup $permissionGroup): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:permission_groups,name,' . $permissionGroup->id],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer'],
        ]);

        $permissionGroup->update($data);

        return redirect()->back()->with('success', 'Security module updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PermissionGroup $permissionGroup): RedirectResponse
    {
        // Re-assign permissions to null group or a "Legacy" group?
        // For now, they become ungrouped.
        $permissionGroup->permissions()->update(['permission_group_id' => null]);
        
        $permissionGroup->delete();

        return redirect()->back()->with('success', 'Security module decommissioned.');
    }
}
