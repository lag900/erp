<?php

namespace App\Providers;

use App\Models\ActivityLog;
use App\Models\Asset;
use App\Models\User;
use App\Observers\AssetObserver;
use App\Observers\UserObserver;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Spatie\Permission\Models\Role;

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
        Asset::observe(AssetObserver::class);
        User::observe(UserObserver::class);

        Event::listen(Login::class, function ($event) {
            ActivityLog::create([
                'user_id' => $event->user->id,
                'action' => 'login',
                'description' => 'User logged in',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                ActivityLog::create([
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
        Gate::before(function (User $user, $ability) {
            return $user->hasRole('SuperAdmin') ? true : null;
        });

        Gate::define('manage-asset-visibility', function (User $user) {
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
            Cache::remember('superadmin_provisioned', 3600, function () {
                try {
                    if (Schema::hasTable('users')) {
                        $exists = User::role('SuperAdmin')->exists() || User::where('email', '1@1.com')->exists();
                        
                        if (!$exists) {
                            $user = User::create([
                                'name' => 'System Owner',
                                'email' => '1@1.com',
                                'password' => Hash::make('1@1.com'),
                                'email_verified_at' => now(),
                                'role' => 'SuperAdmin',
                                'is_active' => true,
                            ]);

                            $role = Role::where('name', 'SuperAdmin')->first();
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
