<?php

use App\Modules\Projects\Controllers\ProjectController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('projects')
    ->name('projects.')
    ->group(function (): void {
        Route::get('/', [ProjectController::class, 'index'])
            ->middleware('permission:list-project')
            ->name('index');

        Route::post('/', [ProjectController::class, 'store'])
            ->name('store');

        Route::delete('/bulk-delete', [
            ProjectController::class,
            'bulkDestroy',
        ])->name('bulk-destroy');

        Route::get('/{project}', [
            ProjectController::class,
            'show',
        ])->name('show');

        Route::put('/{project}', [
            ProjectController::class,
            'update',
        ])->name('update');

        Route::delete('/{project}', [
            ProjectController::class,
            'destroy',
        ])->name('destroy');
    });