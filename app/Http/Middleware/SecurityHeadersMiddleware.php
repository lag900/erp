<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only apply security headers to HTML responses
        if ($this->shouldApplyHeaders($response)) {
            $this->applySecurityHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Determine if security headers should be applied
     */
    private function shouldApplyHeaders(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html') || empty($contentType);
    }

    /**
     * Apply comprehensive security headers
     */
    private function applySecurityHeaders(Response $response, Request $request): void
    {
        $isProduction = app()->environment('production');
        
        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        
        // Prevent MIME-sniffing attacks
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Enable XSS protection (legacy browsers)
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Referrer policy for privacy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Permissions policy (formerly Feature-Policy)
        $response->headers->set('Permissions-Policy', implode(', ', [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
        ]));

        // Content Security Policy (CSP)
        // In development, we need to allow Vite dev server and HMR
        $isDevelopment = app()->environment('local', 'development');
        
        if ($isDevelopment) {
            // Relaxed CSP for development (allows Vite HMR)
            $cspDirectives = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' http://localhost:* http://127.0.0.1:*",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: https: http: blob:",
                "connect-src 'self' ws://localhost:* ws://127.0.0.1:* http://localhost:* http://127.0.0.1:*",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
            ];
        } else {
            // Strict CSP for production
            $cspDirectives = [
                "default-src 'self'",
                "script-src 'self' 'unsafe-inline' 'unsafe-eval'", // Note: Remove unsafe-eval in production after testing
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com",
                "font-src 'self' https://fonts.gstatic.com data:",
                "img-src 'self' data: https: blob:",
                "connect-src 'self'",
                "frame-ancestors 'self'",
                "base-uri 'self'",
                "form-action 'self'",
            ];
        }
        
        $response->headers->set('Content-Security-Policy', implode('; ', $cspDirectives));

        // HSTS (HTTP Strict Transport Security) - Only in production with HTTPS
        if ($isProduction && $request->secure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Remove server information disclosure
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');
    }
}
