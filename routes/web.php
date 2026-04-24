<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redirect root ke login atau dashboard
Route::get('/', function () {
    return auth()->check() ? redirect('/dashboard') : redirect('/login');
});

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
});

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return inertia('Dashboard/Index');
    })->name('dashboard');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    // Master Data
    Route::prefix('master')->name('master.')->group(function () {
        // 1. Kategori Tiket
        Route::resource('categories', \App\Http\Controllers\Master\CategoryController::class)->except(['show', 'destroy'])
            ->middleware('permission:master.category');
        Route::patch('categories/{category}/toggle-active', [\App\Http\Controllers\Master\CategoryController::class, 'toggleActive'])
            ->name('categories.toggle-active')->middleware('permission:master.category');

        // 2. Prioritas Tiket
        Route::resource('priorities', \App\Http\Controllers\Master\PriorityController::class)->except(['show', 'destroy'])
            ->middleware('permission:master.priority');
        Route::patch('priorities/{priority}/toggle-active', [\App\Http\Controllers\Master\PriorityController::class, 'toggleActive'])
            ->name('priorities.toggle-active')->middleware('permission:master.priority');

        // 3. Unit Kerja
        Route::resource('work-units', \App\Http\Controllers\Master\WorkUnitController::class)->except(['show', 'destroy'])
            ->middleware('permission:master.work-unit');
        Route::patch('work-units/{work_unit}/toggle-active', [\App\Http\Controllers\Master\WorkUnitController::class, 'toggleActive'])
            ->name('work-units.toggle-active')->middleware('permission:master.work-unit');
        // Kelola Anggota Unit Kerja
        Route::get('work-units/{work_unit}/members', [\App\Http\Controllers\Master\WorkUnitController::class, 'members'])
            ->name('work-units.members')->middleware('permission:master.work-unit');
        Route::post('work-units/{work_unit}/members', [\App\Http\Controllers\Master\WorkUnitController::class, 'storeMember'])
            ->name('work-units.members.store')->middleware('permission:master.work-unit');
        Route::delete('work-units/{work_unit}/members/{user}', [\App\Http\Controllers\Master\WorkUnitController::class, 'destroyMember'])
            ->name('work-units.members.destroy')->middleware('permission:master.work-unit');

        // 4. User & Role
        Route::resource('users', \App\Http\Controllers\Master\UserController::class)->except(['show', 'destroy'])
            ->middleware('permission:master.user');
        Route::patch('users/{user}/toggle-active', [\App\Http\Controllers\Master\UserController::class, 'toggleActive'])
            ->name('users.toggle-active')->middleware('permission:master.user');

        // 5. Permission per Role
        Route::get('permissions', [\App\Http\Controllers\Master\PermissionController::class, 'index'])
            ->name('permissions.index')->middleware('permission:master.permission');
        Route::post('permissions', [\App\Http\Controllers\Master\PermissionController::class, 'update'])
            ->name('permissions.update')->middleware('permission:master.permission');
    });
});
