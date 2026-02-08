<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Global API rate limiting
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Too many requests. Please slow down.',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // Strict login rate limiting (prevent brute force)
        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;
            $key = 'login:' . strtolower($email) . ':' . $request->ip();
            
            return [
                // 5 attempts per minute
                Limit::perMinute(5)->by($key)->response(function () use ($request) {
                    return back()->withErrors([
                        'email' => 'Too many login attempts. Please try again in 1 minute.',
                    ])->withInput($request->only('email'));
                }),
                // 20 attempts per hour
                Limit::perHour(20)->by($key)->response(function () use ($request) {
                    return back()->withErrors([
                        'email' => 'Account temporarily locked due to multiple failed attempts. Please try again later.',
                    ])->withInput($request->only('email'));
                }),
            ];
        });

        // Password reset rate limiting
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(3)->by($request->ip())
                ->response(function () {
                    return back()->withErrors([
                        'email' => 'Too many password reset requests. Please try again later.',
                    ]);
                });
        });

        // General web rate limiting (generous for normal users)
        RateLimiter::for('web', function (Request $request) {
            return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
        });

        // Admin actions rate limiting (more generous for authenticated admins)
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(200)->by($request->user()?->id ?: $request->ip());
        });

        // File upload rate limiting
        RateLimiter::for('uploads', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip())
                ->response(function () {
                    return back()->with('error', 'Too many file uploads. Please wait before uploading more files.');
                });
        });

        // Search/filter rate limiting (prevent scraping)
        RateLimiter::for('search', function (Request $request) {
            return Limit::perMinute(30)->by($request->user()?->id ?: $request->ip());
        });
    }
}
