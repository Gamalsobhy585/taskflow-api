<?php

namespace App\Modules\Tasks\Repositories\Interface;

use App\Models\Project;
use App\Models\Task;
use App\Modules\Tasks\DTOs\CreateTaskData;
use App\Modules\Tasks\DTOs\TaskFilterData;
use App\Modules\Tasks\DTOs\UpdateTaskData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ITaskRepository
{
    public function findOwnedProject(
        int $projectId,
        int $userId
    ): ?Project;

    public function create(CreateTaskData $data): Task;

    public function paginateForUser(
        TaskFilterData $filters
    ): LengthAwarePaginator;

    public function findForUser(
        int $taskId,
        int $userId
    ): ?Task;

    public function update(
        Task $task,
        UpdateTaskData $data
    ): Task;

    public function delete(Task $task): bool;

    /**
     * @param array<int, int> $taskIds
     */
    public function bulkDeleteForUser(
        int $userId,
        array $taskIds
    ): int;

    public function getOverdueUnnotifiedTasks(
        int $limit = 500
    ): Collection;

    public function markAsOverdueNotified(Task $task): bool;
}