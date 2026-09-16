<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\QadModuleController;

// Dashboard
Route::get('/', [DashboardController::class, 'index'])
    ->name('dashboard');

// Users
Route::get('/users', [UserController::class, 'index'])->name('users.index');
Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
Route::get('/api/users/search', [UserController::class, 'search'])->name('users.search');

// Activity
Route::get('/activity', [ActivityController::class, 'index'])->name('activity.index');
Route::get('/activity/export', [ActivityController::class, 'export'])->name('activity.export');
Route::get('/activity/statistics', [ActivityController::class, 'statistics'])->name('activity.statistics');
Route::get('/activity/{id}', [ActivityController::class, 'show'])->name('activity.show');

// Modules
Route::get('/modules', [QadModuleController::class, 'index'])->name('modules.index');
Route::get('/modules/{id}', [QadModuleController::class, 'show'])->name('modules.show');
Route::get('/api/modules/{id}/overview', [QadModuleController::class, 'overview'])->name('modules.overview');
Route::get('/api/modules/{id}/usage', [QadModuleController::class, 'usageData'])->name('modules.usage');
Route::get('/api/modules/{id}/trend', [QadModuleController::class, 'activityTrendData'])->name('modules.trend');