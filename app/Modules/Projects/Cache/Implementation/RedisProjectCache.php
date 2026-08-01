<?php

namespace App\Modules\Projects\Cache\Implementation;

use App\Models\Project;
use App\Modules\Projects\Cache\Interface\IProjectCache;
use Closure;
use Illuminate\Cache\TaggedCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class RedisProjectCache implements IProjectCache
{
    private const CACHE_TTL_SECONDS = 600;

    public function rememberList(
        int $userId,
        int $page,
        int $perPage,
        Closure $callback
    ): LengthAwarePaginator {
        return $this->userCache($userId)->remember(
            $this->listKey(
                page: $page,
                perPage: $perPage
            ),
            self::CACHE_TTL_SECONDS,
            $callback
        );
    }

    public function rememberProject(
        int $userId,
        int $projectId,
        Closure $callback
    ): ?Project {
        return $this->userCache($userId)->remember(
            $this->projectKey($projectId),
            self::CACHE_TTL_SECONDS,
            $callback
        );
    }

    public function flushForUser(int $userId): void
    {
        $this->userCache($userId)->flush();
    }

    private function userCache(int $userId): TaggedCache
    {
        return Cache::store('redis')->tags([
            "projects:user:{$userId}",
        ]);
    }

    private function listKey(
        int $page,
        int $perPage
    ): string {
        return "projects:list:page:{$page}:per_page:{$perPage}";
    }

    private function projectKey(int $projectId): string
    {
        return "projects:item:{$projectId}";
    }
}