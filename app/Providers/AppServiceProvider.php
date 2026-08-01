<?php

namespace App\Providers;

use App\Modules\Authentication\Listeners\LogAuthenticationActivity;
use App\Modules\Authentication\Repositories\Implementation\UserRepository;
use App\Modules\Authentication\Repositories\Interface\IUser;
use App\Modules\Authentication\Services\Implementations\AuthService;
use App\Modules\Authentication\Services\Interface\IAuthService;
use App\Modules\Projects\Cache\Implementation\RedisProjectCache;
use App\Modules\Projects\Cache\Interface\IProjectCache;
use App\Modules\Projects\Repositories\Implementation\ProjectRepository;
use App\Modules\Projects\Repositories\Interface\IProjectRepository;
use App\Modules\Projects\Services\Implementation\ProjectService;
use App\Modules\Projects\Services\Interface\IProjectService;
use App\Modules\Tasks\Cache\Implementation\RedisTaskCache;
use App\Modules\Tasks\Cache\Interface\ITaskCache;
use App\Modules\Tasks\Repositories\Implementation\TaskRepository;
use App\Modules\Tasks\Repositories\Interface\ITaskRepository;
use App\Modules\Tasks\Services\Implementation\TaskService;
use App\Modules\Tasks\Services\Interface\ITaskService;
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
          $this->app->bind(
            IProjectRepository::class,
            ProjectRepository::class
        );

        $this->app->bind(
            IProjectService::class,
            ProjectService::class
        );
          $this->app->bind(
            IProjectCache::class,
            RedisProjectCache::class
        );
        $this->app->bind(
            ITaskRepository::class,
            TaskRepository::class
        );

        $this->app->bind(
            ITaskService::class,
            TaskService::class
        );
        $this->app->bind(
            ITaskCache::class,
            RedisTaskCache::class
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