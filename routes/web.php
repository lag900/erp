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
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

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

    Route::get('/locations', [LocationsController::class, 'index'])
        ->middleware('permission:location-list')
        ->name('locations.index');
    Route::get('/locations/create', [LocationsController::class, 'create'])
        ->middleware('permission:location-create')
        ->name('locations.create');
    Route::post('/locations', [LocationsController::class, 'store'])
        ->middleware('permission:location-create')
        ->name('locations.store');
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
    Route::delete('/permissions/{permission}', [PermissionsController::class, 'destroy'])
        ->middleware('permission:permission-delete')
        ->name('permissions.destroy');
});

require __DIR__.'/auth.php';
