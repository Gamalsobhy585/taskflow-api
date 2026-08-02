<?php

namespace App\Modules\Tasks\Services\Implementation;

use App\Models\Task;
use App\Models\User;
use App\Modules\Tasks\Cache\Interface\ITaskCache;
use App\Modules\Tasks\DTOs\BulkDeleteTasksData;
use App\Modules\Tasks\DTOs\CreateTaskData;
use App\Modules\Tasks\DTOs\TaskFilterData;
use App\Modules\Tasks\DTOs\UpdateTaskData;
use App\Modules\Tasks\Exceptions\ProjectNotFoundException;
use App\Modules\Tasks\Exceptions\TaskDeletionFailedException;
use App\Modules\Tasks\Exceptions\TaskNotFoundException;
use App\Modules\Tasks\Repositories\Interface\ITaskRepository;
use App\Modules\Tasks\Services\Interface\ITaskService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TaskService implements ITaskService
{
    public function __construct(
        private readonly ITaskRepository $taskRepository,
        private readonly ITaskCache $taskCache
    ) {
    }

    public function create(
        User $user,
        CreateTaskData $data
    ): Task {
        $project = $this->taskRepository
            ->findOwnedProject(
                projectId: $data->projectId,
                userId: $user->id
            );

        if (!$project) {
            throw new ProjectNotFoundException();
        }

        $task = DB::transaction(
            fn (): Task => $this->taskRepository
                ->create($data)
        );

        /*
         * A new task changes all task-list results
         * belonging to this user.
         */
        $this->taskCache->forgetUserTasks(
            $user->id
        );

        return $task;
    }

    public function list(
        TaskFilterData $filters
    ): LengthAwarePaginator {
        return $this->taskCache->rememberTaskList(
            $filters,
            fn (): LengthAwarePaginator => $this
                ->taskRepository
                ->paginateForUser($filters)
        );
    }

    public function find(
        int $taskId,
        User $user
    ): Task {
        return $this->taskCache->rememberTask(
            userId: $user->id,
            taskId: $taskId,
            callback: function () use (
                $taskId,
                $user
            ): Task {
                $task = $this->taskRepository
                    ->findForUser(
                        taskId: $taskId,
                        userId: $user->id
                    );

                if (!$task) {
                    throw new TaskNotFoundException();
                }

                return $task;
            }
        );
    }
    public function update(
        int $taskId,
        User $user,
        UpdateTaskData $data
    ): Task {
        $task = $this->find(
            taskId: $taskId,
            user: $user
        );

        $updatedTask = DB::transaction(
            fn (): Task => $this->taskRepository
                ->update($task, $data)
        );

        $this->taskCache->forgetUserTasks(
            $user->id
        );

        return $updatedTask;
    }
    public function delete(
        int $taskId,
        User $user
    ): void {
        $task = $this->find(
            taskId: $taskId,
            user: $user
        );

        $deleted = DB::transaction(
            fn (): bool => $this->taskRepository
                ->delete($task)
        );

        if (!$deleted) {
            throw new TaskDeletionFailedException();
        }

        $this->taskCache->forgetUserTasks(
            $user->id
        );
    }

    public function bulkDelete(
        BulkDeleteTasksData $data
    ): int {
        $deletedCount = DB::transaction(
            fn (): int => $this->taskRepository
                ->bulkDeleteForUser(
                    userId: $data->userId,
                    taskIds: $data->taskIds
                )
        );

        if ($deletedCount > 0) {
            $this->taskCache->forgetUserTasks(
                $data->userId
            );
        }

        return $deletedCount;
    }
}