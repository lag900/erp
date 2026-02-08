<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait LogsActivity
{
    /**
     * Record an activity log entry.
     */
    public static function log($action, $description = null, $subject = null, $properties = [])
    {
        // Don't log if there's no description and no properties
        if (!$description && empty($properties)) {
            return;
        }

        return ActivityLog::create([
            'user_id' => Auth::id() ?? (app()->runningInConsole() ? null : null), // Handle system actions
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject ? (is_string($subject) ? $subject : get_class($subject)) : null,
            'subject_id' => ($subject instanceof Model) ? $subject->id : (is_numeric($subject) ? $subject : null),
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log an activity for the current model instance.
     */
    public function logActivity($action, $description = null, $properties = [])
    {
        return self::log($action, $description, $this, $properties);
    }

    /**
     * Log changes when a model is updated.
     */
    public function logChanges($description = null)
    {
        $changes = $this->getChanges();
        
        // Remove timestamps from changes
        unset($changes['updated_at']);

        if (empty($changes)) {
            return;
        }

        $properties = [
            'attributes' => $changes,
            'old' => array_intersect_key($this->getOriginal(), $changes),
        ];

        return $this->logActivity('updated', $description ?? "Updated " . class_basename($this), $properties);
    }
}
