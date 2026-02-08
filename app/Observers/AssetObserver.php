<?php

namespace App\Observers;

use App\Models\Asset;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    use \App\Traits\LogsActivity;

    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        $name = $asset->subCategory?->name ?? "Asset #{$asset->id}";
        $this->logActivity('created', "Created asset: {$name}", $asset);
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        $name = $asset->subCategory?->name ?? "Asset #{$asset->id}";
        $changes = $asset->getChanges();
        $description = "Updated asset: {$name}";

        if (array_key_exists('group_name', $changes) || array_key_exists('group_type_id', $changes)) {
             $description = "Changed group for asset: {$name}";
        } elseif (array_key_exists('department_id', $changes)) {
             $description = "Transferred asset: {$name}";
        } elseif (array_key_exists('status', $changes)) {
             $description = "Changed status of {$name} to {$asset->status}";
        }

        $asset->logChanges($description);
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        $name = $asset->subCategory?->name ?? "Asset #{$asset->id}";
        $this->logActivity('deleted', "Deleted asset: {$name}", $asset);
    }

    /**
     * Handle the Asset "restored" event.
     */
    public function restored(Asset $asset): void
    {
        $name = $asset->subCategory?->name ?? "Asset #{$asset->id}";
        $this->logActivity('restored', "Restored asset: {$name}", $asset);
    }

    /**
     * Handle the Asset "force deleted" event.
     */
    public function forceDeleted(Asset $asset): void
    {
        $name = $asset->subCategory?->name ?? "Asset #{$asset->id}";
        $this->logActivity('force_deleted', "Permanently deleted asset: {$name}", $asset);
    }

    private function logActivity($action, $description, $asset, $properties = [])
    {
        \App\Models\ActivityLog::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'action' => $action,
            'description' => $description,
            'subject_type' => get_class($asset),
            'subject_id' => $asset->id,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
