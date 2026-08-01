<?php

namespace App\Modules\Tasks\Cache\Implementation;

use App\Models\Task;
use App\Modules\Tasks\Cache\Interface\ITaskCache;
use App\Modules\Tasks\DTOs\TaskFilterData;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class RedisTaskCache implements ITaskCache
{
    private const CACHE_TTL_SECONDS = 3600;

    private CacheRepository $cache;

    public function __construct()
    {
        $this->cache = Cache::store('redis');
    }

    public function rememberTask(
        int $userId,
        int $taskId,
        callable $callback
    ): Task {
        return $this->cache
            ->tags($this->userTags($userId))
            ->remember(
                $this->taskKey($userId, $taskId),
                self::CACHE_TTL_SECONDS,
                $callback
            );
    }

    public function rememberTaskList(
        TaskFilterData $filters,
        callable $callback
    ): LengthAwarePaginator {
        return $this->cache
            ->tags($this->userTags($filters->userId))
            ->remember(
                $this->taskListKey($filters),
                self::CACHE_TTL_SECONDS,
                $callback
            );
    }

    public function forgetTask(
        int $userId,
        int $taskId
    ): void {
        $this->cache
            ->tags($this->userTags($userId))
            ->forget(
                $this->taskKey($userId, $taskId)
            );
    }

    public function forgetUserTasks(int $userId): void
    {
        $this->cache
            ->tags($this->userTags($userId))
            ->flush();
    }

    /**
     * @return array<int, string>
     */
    private function userTags(int $userId): array
    {
        return [
            'tasks',
            'tasks:user:' . $userId,
        ];
    }

    private function taskKey(
        int $userId,
        int $taskId
    ): string {
        return sprintf(
            'tasks:user:%d:task:%d',
            $userId,
            $taskId
        );
    }

  private function taskListKey(
    TaskFilterData $filters
    ): string {
        $parameters = [
            'user_id' => $filters->userId,
            'project_id' => $filters->projectId,
            'status' => $filters->status?->value,
            'priority' => $filters->priority?->value,
            'search' => $filters->search,
            'per_page' => $filters->perPage,
            'page' => $filters->page,
        ];

        return 'tasks:list:' . hash(
            'sha256',
            json_encode(
                $parameters,
                JSON_THROW_ON_ERROR
            )
        );
    }
}