<?php

use App\Modules\Tasks\Controllers\TaskController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')
    ->prefix('tasks')
    ->name('tasks.')
    ->group(function (): void {
        Route::get('/', [
            TaskController::class,
            'index',
        ])->name('index');

        Route::post('/', [
            TaskController::class,
            'store',
        ])->name('store');

        Route::delete('/bulk-delete', [
            TaskController::class,
            'bulkDestroy',
        ])->name('bulk-destroy');

        Route::get('/{task}', [
            TaskController::class,
            'show',
        ])->name('show');

        Route::put('/{task}', [
            TaskController::class,
            'update',
        ])->name('update');

        Route::delete('/{task}', [
            TaskController::class,
            'destroy',
        ])->name('destroy');
    });