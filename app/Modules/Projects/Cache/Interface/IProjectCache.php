<?php

namespace App\Modules\Projects\Cache\Interface;

use App\Models\Project;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IProjectCache
{
    public function rememberList(
        int $userId,
        int $page,
        int $perPage,
        Closure $callback
    ): LengthAwarePaginator;

    public function rememberProject(
        int $userId,
        int $projectId,
        Closure $callback
    ): ?Project;

    public function flushForUser(int $userId): void;
}