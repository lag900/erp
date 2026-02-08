<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuditObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        $this->log($model, 'created', [], $model->toArray());
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        // Only log if something actually changed
        $changes = $model->getChanges();
        
        // Remove timestamps from changes
        unset($changes['updated_at']);
        
        if (empty($changes)) {
            return;
        }

        $original = array_intersect_key($model->getOriginal(), $changes);

        $this->log($model, 'updated', $original, $changes);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        $this->log($model, 'deleted', $model->toArray(), []);
    }

    /**
     * Handle the Model "restored" event.
     */
    public function restored(Model $model): void
    {
        $this->log($model, 'restored', [], $model->toArray());
    }

    /**
     * Helper to log using the service.
     */
    protected function log(Model $model, string $action, array $old = [], array $new = [])
    {
        try {
            $service = app(\App\Services\AuditService::class);
            $module = class_basename($model);
            
            $service->log(
                actionType: $action,
                module: $module,
                model: $model,
                oldValues: $old,
                newValues: $new
            );
        } catch (\Throwable $e) {
            Log::error('Audit Observer failed: ' . $e->getMessage());
        }
    }
}
