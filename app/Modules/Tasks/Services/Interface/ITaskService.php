<?php

namespace App\Modules\Tasks\Services\Interface;

use App\Models\Task;
use App\Models\User;
use App\Modules\Tasks\DTOs\BulkDeleteTasksData;
use App\Modules\Tasks\DTOs\CreateTaskData;
use App\Modules\Tasks\DTOs\TaskFilterData;
use App\Modules\Tasks\DTOs\UpdateTaskData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ITaskService
{
    public function create(
        User $user,
        CreateTaskData $data
    ): Task;

    public function list(
        TaskFilterData $filters
    ): LengthAwarePaginator;

    public function find(
        int $taskId,
        User $user
    ): Task;

    public function update(
        int $taskId,
        User $user,
        UpdateTaskData $data
    ): Task;

    public function delete(
        int $taskId,
        User $user
    ): void;

    public function bulkDelete(
        BulkDeleteTasksData $data
    ): int;
}