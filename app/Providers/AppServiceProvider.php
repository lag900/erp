<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
        \App\Models\Asset::observe(\App\Observers\AssetObserver::class);

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Login::class, function ($event) {
            \App\Models\ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'login',
                'description' => 'User logged in',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        \Illuminate\Support\Facades\Event::listen(\Illuminate\Auth\Events\Logout::class, function ($event) {
            if ($event->user) {
                \App\Models\ActivityLog::create([
                    'user_id' => $event->user->id,
                    'action' => 'logout',
                    'description' => 'User logged out',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ]);
            }
        });

        Vite::prefetch(concurrency: 3);

        // Implicitly grant "SuperAdmin" role all permissions
        // This works in the app by using gate-related functions like auth()->user->can() and @can()
        Gate::before(function (\App\Models\User $user, $ability) {
            return $user->hasRole('SuperAdmin') ? true : null;
        });

        Gate::define('manage-asset-visibility', function (\App\Models\User $user) {
            // Allow admin roles
            if ($user->hasRole('SuperAdmin') || $user->hasRole('Admin')) {
                return true;
            }

            // Allow Administration department (id = 1)
            if ($user->department_id == 1) {
                return true;
            }

            return false;
        });

        // --- PRODUCTION AUTO-PROVISIONING ---
        // Automatically creates a SuperAdmin account if none exists.
        // This runs only in non-console production environments for first-boot safety.
        if (!app()->runningInConsole()) {
            \Illuminate\Support\Facades\Cache::remember('superadmin_provisioned', 3600, function () {
                try {
                    if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                        $exists = \App\Models\User::role('SuperAdmin')->exists() || \App\Models\User::where('email', '1@1.com')->exists();
                        
                        if (!$exists) {
                            $user = \App\Models\User::create([
                                'name' => 'System Owner',
                                'email' => '1@1.com',
                                'password' => \Illuminate\Support\Facades\Hash::make('1@1.com'),
                                'email_verified_at' => now(),
                                'role' => 'SuperAdmin',
                                'is_active' => true,
                            ]);

                            $role = \Spatie\Permission\Models\Role::where('name', 'SuperAdmin')->first();
                            if ($role) {
                                $user->assignRole($role);
                            }
                        }
                    }
                    return true;
                } catch (\Exception $e) {
                    return false; // Table might not exist yet during migration
                }
            });
        }
    }
}
