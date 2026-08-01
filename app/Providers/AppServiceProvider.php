<?php

namespace App\Providers;

use App\Modules\Authentication\Listeners\LogAuthenticationActivity;
use App\Modules\Authentication\Repositories\Implementation\UserRepository;
use App\Modules\Authentication\Repositories\Interface\IUser;
use App\Modules\Authentication\Services\Implementations\AuthService;
use App\Modules\Authentication\Services\Interface\IAuthService;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register application services and dependencies.
     */
    public function register(): void
    {
        $this->app->bind(
            IAuthService::class,
            AuthService::class
        );

        $this->app->bind(
            IUser::class,
            UserRepository::class
        );
    }

    /**
     * Bootstrap application services.
     */
    public function boot(): void
    {
        $listener = app(LogAuthenticationActivity::class);

        Event::listen(
            Login::class,
            fn (Login $event) => $listener->handleLogin($event)
        );

        Event::listen(
            Logout::class,
            fn (Logout $event) => $listener->handleLogout($event)
        );

        Event::listen(
            Failed::class,
            fn (Failed $event) => $listener->handleFailed($event)
        );
    }
}