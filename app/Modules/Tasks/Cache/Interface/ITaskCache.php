<?php

namespace App\Modules\Tasks\Cache\Interface;

use App\Models\Task;
use App\Modules\Tasks\DTOs\TaskFilterData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ITaskCache
{
    public function rememberTask(
        int $userId,
        int $taskId,
        callable $callback
    ): Task;

    public function rememberTaskList(
        TaskFilterData $filters,
        callable $callback
    ): LengthAwarePaginator;

    public function forgetTask(
        int $userId,
        int $taskId
    ): void;

    public function forgetUserTasks(int $userId): void;
}