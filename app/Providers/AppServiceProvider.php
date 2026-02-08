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
        
        // Register Observers for Enterprise Audit Trail
        $auditModels = [
            Asset::class,
            User::class,
            \App\Models\Category::class,
            \App\Models\SubCategory::class,
            \App\Models\Building::class,
            \App\Models\Location::class,
            \App\Models\Room::class,
            \App\Models\Level::class,
            \App\Models\Department::class,
            \App\Models\Permission::class,
        ];

        foreach ($auditModels as $model) {
            $model::observe(\App\Observers\AuditObserver::class);
        }

        // Specific Observers (if they have extra logic besides auditing)
        Asset::observe(AssetObserver::class);
        User::observe(UserObserver::class);

        // Enterprise Auth Auditing
        Event::listen(Login::class, function ($event) {
            app(\App\Services\AuditService::class)->log(
                actionType: 'login',
                module: 'Auth',
                details: "User {$event->user->email} logged in successfully."
            );
        });

        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                app(\App\Services\AuditService::class)->log(
                    actionType: 'logout',
                    module: 'Auth',
                    details: "User {$event->user->email} logged out."
                );
            }
        });

        Event::listen(\Illuminate\Auth\Events\Failed::class, function ($event) {
            app(\App\Services\AuditService::class)->log(
                actionType: 'failed_login',
                module: 'Auth',
                status: 'failed',
                details: "Failed login attempt for email: " . ($event->credentials['email'] ?? 'unknown'),
                newValues: ['email' => $event->credentials['email'] ?? null]
            );
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
        // Automatically creates SuperAdmin role, permissions and account if missing.
        if (!app()->runningInConsole() || app()->runningUnitTests()) {
            Cache::remember('rbac_provisioned', 3600, function () {
                try {
                    if (Schema::hasTable('roles') && Schema::hasTable('permissions')) {
                        // 1. Ensure SuperAdmin Role exists
                        $superAdminRole = Role::firstOrCreate(['name' => 'SuperAdmin', 'guard_name' => 'web']);

                        // 2. Ensure Governance Permission Group exists
                        $govGroup = \App\Models\PermissionGroup::firstOrCreate(
                            ['name' => 'Governance'],
                            ['description' => 'System Governance and Audit Monitoring', 'icon' => 'shield-check', 'sort_order' => 100]
                        );

                        // 3. Define Enterprise Governance Permissions
                        $govPermissions = [
                            'governance.view' => ['label' => 'System Governance', 'route' => 'dashboard', 'icon' => 'cog'],
                            'governance.manage' => ['label' => 'Manage Governance', 'route' => 'dashboard', 'icon' => 'adjustments'],
                            'audit.view' => ['label' => 'Audit Trail', 'route' => 'audit.index', 'icon' => 'eye'],
                            'audit.full' => ['label' => 'System Audit Full', 'route' => 'audit.index', 'icon' => 'collection'],
                            'security.alerts.view' => ['label' => 'Security Center', 'route' => 'audit.alerts', 'icon' => 'shield-exclamation'],
                            'security.alerts.manage' => ['label' => 'Security Management', 'route' => 'audit.alerts', 'icon' => 'lock-closed'],
                        ];

                        foreach ($govPermissions as $pName => $pData) {
                            \App\Models\Permission::firstOrCreate(
                                ['name' => $pName, 'guard_name' => 'web'],
                                [
                                    'permission_group_id' => $govGroup->id,
                                    'sidebar_label' => $pData['label'],
                                    'route_name' => $pData['route'],
                                    'icon' => $pData['icon'],
                                    'is_sidebar_item' => str_ends_with($pName, '.view'),
                                    'sort_order' => 10,
                                ]
                            );
                        }

                        // 4. Sync All to SuperAdmin
                        $superAdminRole->givePermissionTo(array_keys($govPermissions));

                        // 5. Ensure System Owner account exists
                        if (Schema::hasTable('users')) {
                            $user = User::where('email', '1@1.com')->first();
                            if (!$user) {
                                $user = User::create([
                                    'name' => 'System Owner',
                                    'email' => '1@1.com',
                                    'password' => Hash::make('1@1.com'),
                                    'email_verified_at' => now(),
                                    'role' => 'SuperAdmin',
                                    'is_active' => true,
                                ]);
                            }
                            $user->assignRole($superAdminRole);
                        }
                    }
                    return true;
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('RBAC Provisioning failed: ' . $e->getMessage());
                    return false;
                }
            });
        }
    }
}
