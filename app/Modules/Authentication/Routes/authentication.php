<?php

use App\Modules\Authentication\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')
    ->name('authentication.')
    ->group(function (): void {
        Route::post('/register', [AuthController::class, 'register'])
            ->name('register');

        Route::post('/login', [AuthController::class, 'login'])
            ->name('login');

        Route::middleware('auth:sanctum')
            ->group(function (): void {
                Route::post('/logout', [AuthController::class, 'logout'])
                    ->name('logout');

                Route::put('/renew-password', [
                    AuthController::class,
                    'renewPassword',
                ])->name('renew-password');

                Route::get('/user-info', [
                    AuthController::class,
                    'getUserInfo',
                ])->name('user-info');
            });
    });