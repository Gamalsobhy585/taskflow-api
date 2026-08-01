<?php

namespace Tests\Concerns;

use App\Modules\Tasks\Cache\Interface\ITaskCache;
use Mockery;
use Mockery\MockInterface;

trait MocksTaskCache
{
    protected ITaskCache&MockInterface $taskCacheMock;

    protected function mockTaskCache(): void
    {
        $this->taskCacheMock = Mockery::mock(
            ITaskCache::class
        );

        /*
         * Simulate a cache miss for a single task.
         *
         * The callback will execute and return the value
         * produced by the real repository/service flow.
         */
        $this->taskCacheMock
            ->shouldReceive('rememberTask')
            ->zeroOrMoreTimes()
            ->andReturnUsing(
                static function (
                    int $userId,
                    int $taskId,
                    callable $callback
                ) {
                    return $callback();
                }
            );

        /*
         * Simulate a cache miss for paginated task lists.
         *
         * This lets feature tests execute the real repository
         * query without requiring an active Redis server.
         */
        $this->taskCacheMock
            ->shouldReceive('rememberTaskList')
            ->zeroOrMoreTimes()
            ->andReturnUsing(
                static function (
                    mixed $filters,
                    callable $callback
                ) {
                    return $callback();
                }
            );

        /*
         * Cache invalidation methods do nothing during
         * feature tests, but they are allowed to be called.
         */
        $this->taskCacheMock
            ->shouldReceive('forgetTask')
            ->zeroOrMoreTimes()
            ->andReturnNull();

        $this->taskCacheMock
            ->shouldReceive('forgetUserTasks')
            ->zeroOrMoreTimes()
            ->andReturnNull();

        /*
         * Replace the real Redis cache implementation
         * in Laravel's service container.
         */
        $this->app->instance(
            ITaskCache::class,
            $this->taskCacheMock
        );
    }
}