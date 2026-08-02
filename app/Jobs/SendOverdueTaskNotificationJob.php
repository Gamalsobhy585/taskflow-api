<?php

namespace App\Jobs;

use App\Models\Task;
use App\Modules\Tasks\Cache\Interface\ITaskCache;
use App\Modules\Tasks\Repositories\Interface\ITaskRepository;
use App\Notifications\OverdueTaskNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class SendOverdueTaskNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(
        public readonly int $taskId
    ) {
    }

    public function handle(
        ITaskRepository $taskRepository,
        ITaskCache $taskCache
    ): void {
        $userId = DB::transaction(
            function () use (
                $taskRepository
            ): ?int {
                $task = Task::query()
                    ->with([
                        'project:id,user_id,name',
                        'project.user:id,name,email',
                    ])
                    ->lockForUpdate()
                    ->find($this->taskId);

                if (
                    !$task
                    || !$task->isOverdue()
                    || $task->overdue_notified_at !== null
                    || !$task->project?->user
                ) {
                    return null;
                }

                $task->project->user->notify(
                    new OverdueTaskNotification($task)
                );

                $taskRepository
                    ->markAsOverdueNotified($task);

                return $task->project->user_id;
            }
        );

        if ($userId !== null) {
            $taskCache->forgetUserTasks($userId);
        }
    }
}