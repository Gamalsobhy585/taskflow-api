<?php

use App\Modules\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth:sanctum',
    'permission:view-dashboard',
])
    ->get('/dashboard', DashboardController::class)
    ->name('dashboard.statistics');