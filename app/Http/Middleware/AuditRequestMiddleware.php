<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\AuditService;

class AuditRequestMiddleware
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log state-changing requests or unauthorized attempts
        if ($this->shouldLog($request, $response)) {
            $this->auditService->log(
                actionType: $this->getActionType($request, $response),
                module: $this->getModuleName($request),
                status: $response->isSuccessful() ? 'success' : 'failed',
                details: "Request to " . $request->path()
            );
        }

        return $response;
    }

    protected function shouldLog(Request $request, Response $response): bool
    {
        // Always log failed attempts (401, 403)
        if ($response->getStatusCode() === 401 || $response->getStatusCode() === 403) {
            return true;
        }

        // Log POST, PUT, PATCH, DELETE
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            // Exclude common non-critical paths
            $excluded = ['broadcasting/auth', 'livewire/*', 'sanctum/*', '*/notifications/*'];
            foreach ($excluded as $path) {
                if ($request->is($path)) return false;
            }
            return true;
        }

        // Log sensitive GET requests (like exports or user management)
        if ($request->is('admin/*', 'users/*', 'roles/*', 'permissions/*', '*/export')) {
            return true;
        }

        return false;
    }

    protected function getActionType(Request $request, Response $response): string
    {
        if ($response->getStatusCode() === 403) return 'permission_denied';
        if ($response->getStatusCode() === 401) return 'unauthorized_access';
        
        return match ($request->method()) {
            'POST' => 'create_request',
            'PUT', 'PATCH' => 'update_request',
            'DELETE' => 'delete_request',
            default => 'view_request',
        };
    }

    protected function getModuleName(Request $request): string
    {
        $segments = $request->segments();
        // Handle nested modules
        if (count($segments) > 1 && $segments[0] === 'api') {
            return ucfirst($segments[1]);
        }
        return count($segments) > 0 ? ucfirst($segments[0]) : 'System';
    }
}
