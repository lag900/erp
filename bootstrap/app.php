<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
            \App\Http\Middleware\SecurityHeadersMiddleware::class,
            \App\Http\Middleware\CompressResponse::class, // Gzip compression for production
            \App\Http\Middleware\AuditRequestMiddleware::class,
        ]);

        $middleware->alias([
            'department.selected' => \App\Http\Middleware\EnsureDepartmentSelected::class,
            'feature.enabled' => \App\Http\Middleware\EnsureFeatureEnabled::class,
            'role' => \App\Http\Middleware\RoleMiddleware::class, // Updated to simple role middleware
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        //
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response) {
            if ($response->getStatusCode() === 419) {
                return back()->with([
                    'message' => 'The page expired, please try again.',
                ]);
            }
 
            return $response;
        });

        // Custom Error Pages via Inertia
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return null;
            }

            if ($request->header('X-Inertia')) {
                $status = match (true) {
                    $e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException => 405, // Specifically handle method mismatch
                    $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException => 404,
                    $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException => 404,
                    $e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface => $e->getStatusCode(),
                    default => 500
                };

                // Only render nice error pages for 403, 404, 405, 500, 503
                if (in_array($status, [403, 404, 405, 500, 503])) {
                    return inertia('Error', ['status' => $status])
                        ->toResponse($request)
                        ->setStatusCode($status);
                }
            }
            // Fallback for non-Inertia requests calls parent render
            return null; 
        });
    })->create();
