<?php

namespace App\Modules\Tasks\Repositories\Implementation;

use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Modules\Tasks\DTOs\CreateTaskData;
use App\Modules\Tasks\DTOs\TaskFilterData;
use App\Modules\Tasks\DTOs\UpdateTaskData;
use App\Modules\Tasks\Repositories\Interface\ITaskRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class TaskRepository implements ITaskRepository
{
    public function findOwnedProject(
        int $projectId,
        int $userId
    ): ?Project {
        return Project::query()
            ->whereKey($projectId)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(CreateTaskData $data): Task
    {
        return Task::query()->create(
            $data->toArray()
        );
    }

    public function paginateForUser(
        TaskFilterData $filters
    ): LengthAwarePaginator {
        return Task::query()
            ->whereHas(
                'project',
                fn ($query) => $query->where(
                    'user_id',
                    $filters->userId
                )
            )
            ->when(
                $filters->projectId,
                fn ($query, int $projectId) => $query
                    ->where('project_id', $projectId)
            )
            ->when(
                $filters->status,
                fn ($query) => $query->where(
                    'status',
                    $filters->status->value
                )
            )
            ->when(
                $filters->priority,
                fn ($query) => $query->where(
                    'priority',
                    $filters->priority->value
                )
            )
            ->when(
                $filters->search,
                fn ($query, string $search) => $query
                    ->where(
                        'title',
                        'like',
                        '%' . $search . '%'
                    )
            )
            ->with('project:id,user_id,name')
            ->latest('id')
            ->paginate(
                perPage: $filters->perPage,
                page: $filters->page
            );
    }

    public function findForUser(
        int $taskId,
        int $userId
    ): ?Task {
        return Task::query()
            ->whereKey($taskId)
            ->whereHas(
                'project',
                fn ($query) => $query->where(
                    'user_id',
                    $userId
                )
            )
            ->with('project:id,user_id,name')
            ->first();
    }

    public function update(
        Task $task,
        UpdateTaskData $data
    ): Task {
        $task->update(
            $data->toArray()
        );

        return $task->refresh();
    }

    public function delete(Task $task): bool
    {
        return (bool) $task->delete();
    }

    public function bulkDeleteForUser(
        int $userId,
        array $taskIds
    ): int {
        return Task::query()
            ->whereIn('id', $taskIds)
            ->whereHas(
                'project',
                fn ($query) => $query->where(
                    'user_id',
                    $userId
                )
            )
            ->delete();
    }

    public function getOverdueUnnotifiedTasks(
        int $limit = 500
    ): Collection {
        return Task::query()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', TaskStatus::DONE->value)
            ->whereNull('overdue_notified_at')
            ->whereHas('project.user')
            ->with([
                'project:id,user_id,name',
                'project.user:id,name,email',
            ])
            ->limit($limit)
            ->get();
    }

    public function markAsOverdueNotified(Task $task): bool
    {
        return $task->update([
            'overdue_notified_at' => now(),
        ]);
    }
}