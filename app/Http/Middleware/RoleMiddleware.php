<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized action.');
        }

        $allowedRoles = explode('|', $roles);
        $userRole = strtolower($request->user()->role);
        
        $hasAccess = false;
        foreach ($allowedRoles as $role) {
            $normalized = strtolower($role);
            $snake = \Illuminate\Support\Str::snake($role);
            
            if ($userRole === $normalized || $userRole === $snake) {
                $hasAccess = true;
                break;
            }
        }
        
        // Also check Spatie roles if not matched by simple column
        if (!$hasAccess) {
            $hasAccess = $request->user()->hasAnyRole($allowedRoles);
        }

        if (!$hasAccess) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
