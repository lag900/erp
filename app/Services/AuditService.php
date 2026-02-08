<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class AuditService
{
    /**
     * Log user activity or system event.
     *
     * @param string $actionType (e.g. create, update, delete, login, logout, failed_login, permission_denied)
     * @param string $module (e.g. User Management, Assets, Settings)
     * @param Model|null $model The affected model
     * @param array|null $oldValues Data before change
     * @param array|null $newValues Data after change
     * @param string $status (success, failed)
     * @param string|null $details Extra text details
     */
    public function log(
        string $actionType,
        string $module,
        $model = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        string $status = 'success',
        ?string $details = null
    ): void {
        try {
            $user = Auth::user();
            $severity = $this->determineSeverity($actionType, $newValues, $status);

            AuditLog::create([
                'user_id' => $user->id ?? null,
                'user_name' => $user->name ?? 'System/Guest',
                'role' => $this->getUserRole($user),
                'action_type' => $actionType,
                'module' => $module,
                'auditable_type' => ($model instanceof Model) ? get_class($model) : (is_object($model) ? get_class($model) : null),
                'auditable_id' => ($model instanceof Model || (is_object($model) && isset($model->id))) ? $model->id : null,
                'old_values' => $this->filterSensitiveData($oldValues),
                'new_values' => $this->filterSensitiveData($newValues),
                'ip_address' => Request::ip() ?? request()->ip(),
                'user_agent' => substr(Request::header('User-Agent') ?? request()->header('User-Agent'), 0, 500),
                'url' => Request::fullUrl() ?? request()->fullUrl(),
                'status' => $status,
                'severity' => $severity,
            ]);

        } catch (\Throwable $e) {
            // Prevent audit logging failures from stopping the application
            Log::error('Audit logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Determine severity based on action and values.
     */
    private function determineSeverity(string $actionType, ?array $newValues, string $status): string
    {
        if ($status === 'failed') {
            return 'warning';
        }

        $criticalActions = [
            'permission_denied',
            'failed_login',
            'mass_delete',
            'security_alert',
            'unauthorized_access'
        ];

        if (in_array($actionType, $criticalActions)) {
            return 'warning';
        }

        // Detect if role changed to SuperAdmin
        if ($actionType === 'updated' || $actionType === 'created') {
            if (isset($newValues['role']) && $newValues['role'] === 'SuperAdmin') {
                return 'critical';
            }
            if (isset($newValues['role_id']) && $newValues['role_id'] == 1) {
                return 'critical';
            }
        }

        // Massive delete detection (passed via details or properties in special cases)
        if ($actionType === 'mass_delete' || (isset($newValues['count']) && $newValues['count'] > 50)) {
            return 'critical';
        }

        return 'info';
    }

    /**
     * Get user role at the time of action.
     */
    private function getUserRole(?User $user): string
    {
        if (!$user) return 'Guest';
        
        // Try getting role from the model property if it exists
        if (isset($user->role)) return $user->role;
        
        // Fallback to Spatie Permission roles if available
        if (method_exists($user, 'getRoleNames')) {
            $role = $user->getRoleNames()->first();
            return $role ?: 'User';
        }

        return 'User';
    }

    /**
     * Filter sensitive fields like passwords.
     */
    private function filterSensitiveData(?array $data): ?array
    {
        if (!$data) return null;
        
        $sensitive = [
            'password', 
            'password_confirmation', 
            'remember_token', 
            'api_token', 
            'secret', 
            'otp', 
            'two_factor_secret'
        ];
        
        foreach ($sensitive as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[HIDDEN]';
            }
        }
        
        return $data;
    }
}
