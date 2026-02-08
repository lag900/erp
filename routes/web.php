<?php

use App\Http\Controllers\AssetsController;
use App\Http\Controllers\BuildingsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentFeaturesController;
use App\Http\Controllers\DepartmentMembersController;
use App\Http\Controllers\DepartmentSelectionController;
use App\Http\Controllers\DepartmentsController;
use App\Http\Controllers\LevelsController;
use App\Http\Controllers\LocationsController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\RoomsController;
use App\Http\Controllers\SubCategoriesController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\Media\MediaSettingsController;
use App\Http\Controllers\Media\NewsController;
use App\Http\Controllers\Public\NewsController as PublicNewsController;
use App\Models\News as NewsModel;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    $news = NewsModel::where('status', 'published')
        ->orderBy('publish_date', 'desc')
        ->take(3)
        ->get();

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'initialNews' => $news,
    ]);
});

Route::get('/api/news', [PublicNewsController::class, 'index'])->name('api.news.index');
Route::get('/api/news/{id}', [PublicNewsController::class, 'show'])->name('api.news.show');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/departments/select', [DepartmentSelectionController::class, 'index'])
        ->name('departments.select');
    Route::post('/departments/select', [DepartmentSelectionController::class, 'store'])
        ->name('departments.select.store');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Media & News Management - Restricted to Media Role or users with explicit permissions
Route::middleware(['auth', 'verified', 'role_or_permission:Media|news-list|media-settings-manage'])->group(function () {
    
    // News Management
    Route::get('/media/news', [NewsController::class, 'index'])->name('media.news.index');
    Route::get('/media/news/create', [NewsController::class, 'create'])->name('media.news.create');
    Route::post('/media/news', [NewsController::class, 'store'])->name('media.news.store');
    Route::get('/media/news/{news}/edit', [NewsController::class, 'edit'])->name('media.news.edit');
    Route::put('/media/news/{news}', [NewsController::class, 'update'])->name('media.news.update');
    Route::delete('/media/news/{news}', [NewsController::class, 'destroy'])->name('media.news.destroy');

    // Media Settings
    Route::get('/media/settings', [MediaSettingsController::class, 'edit'])->name('media.settings.edit');
    Route::put('/media/settings', [MediaSettingsController::class, 'update'])->name('media.settings.update');
});

Route::middleware(['auth', 'verified', 'department.selected', 'feature.enabled:assets'])->group(function () {
    Route::get('/assets', [AssetsController::class, 'index'])
        ->middleware('permission:asset-list')
        ->name('assets.index');
    Route::get('/assets/create', [AssetsController::class, 'create'])
        ->middleware('permission:asset-create')
        ->name('assets.create');
    Route::post('/assets', [AssetsController::class, 'store'])
        ->middleware('permission:asset-create')
        ->name('assets.store');
    Route::get('/assets/{asset}/edit', [AssetsController::class, 'edit'])
        ->middleware('permission:asset-edit')
        ->name('assets.edit');
    Route::get('/assets/{asset}', [AssetsController::class, 'show'])
        ->middleware('permission:asset-list')
        ->name('assets.show');
    Route::put('/assets/{asset}', [AssetsController::class, 'update'])
        ->middleware('permission:asset-edit')
        ->name('assets.update');
    Route::delete('/assets/{asset}', [AssetsController::class, 'destroy'])
        ->middleware('permission:asset-delete')
        ->name('assets.destroy');
    Route::post('/assets/{asset}/transfer', [AssetsController::class, 'transfer'])
        ->middleware('permission:asset-edit')
        ->name('assets.transfer');
    Route::patch('/assets/{asset}/status', [AssetsController::class, 'updateStatus'])
        ->middleware('permission:asset-edit')
        ->name('assets.update-status');
    Route::patch('/assets/{asset}/sharing', [AssetsController::class, 'updateSharing'])
        ->middleware('permission:asset-edit')
        ->name('assets.update-sharing');
    Route::post('/assets/{asset}/components', [AssetsController::class, 'storeComponents'])
        ->middleware('permission:asset-create')
        ->name('assets.store-components');
    Route::get('/assets/{asset}/components', [AssetsController::class, 'addComponents'])
        ->middleware('permission:asset-create')
        ->name('assets.add-components');
    Route::get('/assets/{asset}/group', [AssetsController::class, 'manageGroup'])
        ->middleware('permission:asset-edit')
        ->name('assets.group.manage');
    Route::put('/assets/{asset}/group', [AssetsController::class, 'updateGroup'])
        ->middleware('permission:asset-edit')
        ->name('assets.group.update');
    Route::post('/assets/{asset}/attach', [AssetsController::class, 'attachComponent'])
        ->middleware('permission:asset-edit')
        ->name('assets.attach');
    Route::post('/assets/{asset}/components', [AssetsController::class, 'storeComponent'])
        ->middleware('permission:asset-edit')
        ->name('assets.components.store');
    Route::delete('/assets/{asset}/detach', [AssetsController::class, 'detachComponent'])
        ->middleware('permission:asset-edit')
        ->name('assets.detach');
    Route::get('/api/assets/search', [AssetsController::class, 'searchAssets'])
        ->middleware('permission:asset-list')
        ->name('api.assets.search');

    Route::get('/locations', [LocationsController::class, 'index'])
        ->middleware('permission:location-list')
        ->name('locations.index');
    Route::get('/locations/create', [LocationsController::class, 'create'])
        ->middleware('permission:location-create')
        ->name('locations.create');
    Route::post('/locations', [LocationsController::class, 'store'])
        ->middleware('permission:location-create')
        ->name('locations.store');
    Route::get('/locations/{location}', [LocationsController::class, 'show'])
        ->middleware('permission:location-list')
        ->name('locations.show');
    Route::get('/locations/{location}/edit', [LocationsController::class, 'edit'])
        ->middleware('permission:location-edit')
        ->name('locations.edit');
    Route::put('/locations/{location}', [LocationsController::class, 'update'])
        ->middleware('permission:location-edit')
        ->name('locations.update');
    Route::delete('/locations/{location}', [LocationsController::class, 'destroy'])
        ->middleware('permission:location-delete')
        ->name('locations.destroy');

    Route::get('/buildings', [BuildingsController::class, 'index'])
        ->middleware('permission:building-list')
        ->name('buildings.index');
    Route::get('/buildings/create', [BuildingsController::class, 'create'])
        ->middleware('permission:building-create')
        ->name('buildings.create');
    Route::post('/buildings', [BuildingsController::class, 'store'])
        ->middleware('permission:building-create')
        ->name('buildings.store');
    Route::get('/buildings/{building}', [BuildingsController::class, 'show'])
        ->middleware('permission:building-list')
        ->name('buildings.show');
    Route::get('/buildings/{building}/edit', [BuildingsController::class, 'edit'])
        ->middleware('permission:building-edit')
        ->name('buildings.edit');
    Route::put('/buildings/{building}', [BuildingsController::class, 'update'])
        ->middleware('permission:building-edit')
        ->name('buildings.update');
    Route::delete('/buildings/{building}', [BuildingsController::class, 'destroy'])
        ->middleware('permission:building-delete')
        ->name('buildings.destroy');

    Route::get('/rooms', [RoomsController::class, 'index'])
        ->middleware('permission:room-list')
        ->name('rooms.index');
    Route::get('/rooms/create', [RoomsController::class, 'create'])
        ->middleware('permission:room-create')
        ->name('rooms.create');
    Route::post('/rooms', [RoomsController::class, 'store'])
        ->middleware('permission:room-create')
        ->name('rooms.store');
    Route::get('/rooms/{room}/edit', [RoomsController::class, 'edit'])
        ->middleware('permission:room-edit')
        ->name('rooms.edit');
    Route::put('/rooms/{room}', [RoomsController::class, 'update'])
        ->middleware('permission:room-edit')
        ->name('rooms.update');
    Route::delete('/rooms/{room}', [RoomsController::class, 'destroy'])
        ->middleware('permission:room-delete')
        ->name('rooms.destroy');
    Route::get('/api/rooms', [RoomsController::class, 'getByBuilding'])->name('api.rooms');

    Route::get('/levels', [LevelsController::class, 'index'])
        ->middleware('permission:level-list')
        ->name('levels.index');
    Route::get('/levels/create', [LevelsController::class, 'create'])
        ->middleware('permission:level-create')
        ->name('levels.create');
    Route::post('/levels', [LevelsController::class, 'store'])
        ->middleware('permission:level-create')
        ->name('levels.store');
    Route::get('/levels/{level}/edit', [LevelsController::class, 'edit'])
        ->middleware('permission:level-edit')
        ->name('levels.edit');
    Route::put('/levels/{level}', [LevelsController::class, 'update'])
        ->middleware('permission:level-edit')
        ->name('levels.update');
    Route::delete('/levels/{level}', [LevelsController::class, 'destroy'])
        ->middleware('permission:level-delete')
        ->name('levels.destroy');

    Route::get('/categories', [CategoriesController::class, 'index'])
        ->middleware('permission:category-list')
        ->name('categories.index');
    Route::get('/categories/create', [CategoriesController::class, 'create'])
        ->middleware('permission:category-create')
        ->name('categories.create');
    Route::post('/categories', [CategoriesController::class, 'store'])
        ->middleware('permission:category-create')
        ->name('categories.store');
    Route::get('/categories/{category}/edit', [CategoriesController::class, 'edit'])
        ->middleware('permission:category-edit')
        ->name('categories.edit');
    Route::put('/categories/{category}', [CategoriesController::class, 'update'])
        ->middleware('permission:category-edit')
        ->name('categories.update');
    Route::delete('/categories/{category}', [CategoriesController::class, 'destroy'])
        ->middleware('permission:category-delete')
        ->name('categories.destroy');
    Route::get('/api/categories/{category}/spec-templates', [CategoriesController::class, 'getSpecTemplates'])
        ->name('api.categories.spec-templates');
    Route::post('/api/categories/{category}/rename-spec-template', [CategoriesController::class, 'renameSpecTemplate'])
        ->name('api.categories.rename-spec-template');

    Route::get('/subcategories', [SubCategoriesController::class, 'index'])
        ->middleware('permission:sub_category-list')
        ->name('subcategories.index');
    Route::get('/subcategories/create', [SubCategoriesController::class, 'create'])
        ->middleware('permission:sub_category-create')
        ->name('subcategories.create');
    Route::post('/subcategories', [SubCategoriesController::class, 'store'])
        ->middleware('permission:sub_category-create')
        ->name('subcategories.store');
    Route::get('/subcategories/{subCategory}/edit', [SubCategoriesController::class, 'edit'])
        ->middleware('permission:sub_category-edit')
        ->name('subcategories.edit');
    Route::put('/subcategories/{subCategory}', [SubCategoriesController::class, 'update'])
        ->middleware('permission:sub_category-edit')
        ->name('subcategories.update');
    Route::delete('/subcategories/{subCategory}', [SubCategoriesController::class, 'destroy'])
        ->middleware('permission:sub_category-delete')
        ->name('subcategories.destroy');
    Route::get('/api/subcategories', [SubCategoriesController::class, 'getByCategory'])->name('api.sub-categories');

    // Moving users routes out of assets feature group to a dedicated super_admin group
});

Route::middleware(['auth', 'verified', 'department.selected', 'feature.enabled:reports'])->group(function () {
        Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->middleware('permission:report-access')->name('reports.index');
    Route::get('/reports/{type}', [\App\Http\Controllers\ReportController::class, 'view'])->middleware('permission:report-access')->name('reports.view');
});

// Administration Units - Restricted to SuperAdmin or specific permissions
Route::middleware(['auth', 'verified', 'role:SuperAdmin|Admin'])->group(function () {
    Route::resource('administration', \App\Http\Controllers\AdministrationUnitsController::class)->names([
        'index' => 'administration.index',
        'create' => 'administration.create',
        'store' => 'administration.store',
        'edit' => 'administration.edit',
        'update' => 'administration.update',
        'destroy' => 'administration.destroy',
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/users', [UsersController::class, 'index'])
        ->middleware('permission:user-list')
        ->name('users.index');
    Route::get('/users/create', [UsersController::class, 'create'])
        ->middleware('permission:user-create')
        ->name('users.create');
    Route::post('/users', [UsersController::class, 'store'])
        ->middleware('permission:user-create')
        ->name('users.store');
    Route::get('/users/{user}/edit', [UsersController::class, 'edit'])
        ->middleware('permission:user-edit')
        ->name('users.edit');
    Route::put('/users/{user}', [UsersController::class, 'update'])
        ->middleware('permission:user-edit')
        ->name('users.update');
    Route::delete('/users/{user}', [UsersController::class, 'destroy'])
        ->middleware('permission:user-delete')
        ->name('users.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/departments', [DepartmentsController::class, 'index'])
        ->middleware('permission:department-list')
        ->name('departments.index');
    Route::get('/departments/create', [DepartmentsController::class, 'create'])
        ->middleware('permission:department-create')
        ->name('departments.create');
    Route::post('/departments', [DepartmentsController::class, 'store'])
        ->middleware('permission:department-create')
        ->name('departments.store');
    Route::get('/departments/{department}/edit', [DepartmentsController::class, 'edit'])
        ->middleware('permission:department-edit')
        ->name('departments.edit');
    Route::put('/departments/{department}', [DepartmentsController::class, 'update'])
        ->middleware('permission:department-edit')
        ->name('departments.update');
    Route::delete('/departments/{department}', [DepartmentsController::class, 'destroy'])
        ->middleware('permission:department-delete')
        ->name('departments.destroy');
    Route::get('/departments/{department}/members', [DepartmentMembersController::class, 'index'])
        ->middleware('permission:department-assign-users')
        ->name('departments.members');
    Route::put('/departments/{department}/members', [DepartmentMembersController::class, 'update'])
        ->middleware('permission:department-assign-users')
        ->name('departments.members.update');

    Route::get('/departments/features', [DepartmentFeaturesController::class, 'index'])
        ->middleware('permission:feature-toggle')
        ->name('departments.features');
    Route::put('/departments/{department}/features', [DepartmentFeaturesController::class, 'update'])
        ->middleware('permission:feature-toggle')
        ->name('departments.features.update');

    Route::get('/departments/{department}/branding', [\App\Http\Controllers\DepartmentBrandingController::class, 'edit'])
        ->middleware('permission:branding-manage')
        ->name('departments.branding');
    Route::post('/departments/{department}/branding', [\App\Http\Controllers\DepartmentBrandingController::class, 'update'])
        ->middleware('permission:branding-manage')
        ->name('departments.branding.update');

    Route::get('/roles', [RolesController::class, 'index'])
        ->middleware('permission:role-list')
        ->name('roles.index');
    Route::get('/roles/create', [RolesController::class, 'create'])
        ->middleware('permission:role-create')
        ->name('roles.create');
    Route::post('/roles', [RolesController::class, 'store'])
        ->middleware('permission:role-create')
        ->name('roles.store');
    Route::get('/roles/{role}/edit', [RolesController::class, 'edit'])
        ->middleware('permission:role-edit')
        ->name('roles.edit');
    Route::put('/roles/{role}', [RolesController::class, 'update'])
        ->middleware('permission:role-edit')
        ->name('roles.update');
    Route::delete('/roles/{role}', [RolesController::class, 'destroy'])
        ->middleware('permission:role-delete')
        ->name('roles.destroy');

    Route::get('/permissions', [PermissionsController::class, 'index'])
        ->middleware('permission:permission-list')
        ->name('permissions.index');
    Route::get('/permissions/create', [PermissionsController::class, 'create'])
        ->middleware('permission:permission-create')
        ->name('permissions.create');
    Route::post('/permissions', [PermissionsController::class, 'store'])
        ->middleware('permission:permission-create')
        ->name('permissions.store');
    Route::patch('/permissions/{permission}/move', [PermissionsController::class, 'move'])
        ->middleware('permission:permission-edit')
        ->name('permissions.move');
    Route::post('permissions/bulk-assign', [PermissionsController::class, 'bulkAssign'])
        ->middleware('permission:permission-edit')
        ->name('permissions.bulk-assign');
    Route::post('permissions/bulk-remove', [PermissionsController::class, 'bulkRemove'])
        ->middleware('permission:permission-edit')
        ->name('permissions.bulk-remove');
    Route::post('permissions/bulk-move', [PermissionsController::class, 'bulkMove'])
        ->middleware('permission:permission-edit')
        ->name('permissions.bulk-move');
    Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy'])
        ->middleware('permission:permission-delete')
        ->name('permissions.destroy');

    // Permission Groups Management
    Route::resource('permission-groups', \App\Http\Controllers\PermissionGroupsController::class)
        ->only(['store', 'update', 'destroy'])
        ->middleware('permission:permission-create');

    // Enterprise Audit Trail
    Route::middleware(['role:SuperAdmin'])->group(function () {
        Route::get('/audit', [\App\Http\Controllers\AuditLogsController::class, 'index'])->name('audit.index');
        Route::get('/audit/alerts', [\App\Http\Controllers\AuditLogsController::class, 'securityAlerts'])->name('audit.alerts');
        Route::get('/audit/export', [\App\Http\Controllers\AuditLogsController::class, 'export'])->name('audit.export');
        Route::get('/audit/{log}', [\App\Http\Controllers\AuditLogsController::class, 'show'])->name('audit.show');
        Route::post('/audit/cleanup', [\App\Http\Controllers\AuditLogsController::class, 'cleanup'])->name('audit.cleanup');
    });
});

require __DIR__.'/auth.php';
