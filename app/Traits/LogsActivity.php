<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

trait LogsActivity
{
    /**
     * Record an activity log entry.
     */
    public static function log($action, $description = null, $subject = null, $properties = [])
    {
        try {
            $service = app(\App\Services\AuditService::class);
            $module = $subject ? class_basename($subject) : 'General';
            
            $service->log(
                actionType: $action,
                module: $module,
                model: ($subject instanceof Model) ? $subject : null,
                oldValues: $properties['old'] ?? null,
                newValues: $properties['attributes'] ?? ($properties ?: null),
                status: 'success',
                details: $description
            );
        } catch (\Throwable $e) {
            Log::error('LogsActivity failed: ' . $e->getMessage());
        }
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
