<?php

namespace App\Console\Commands;

use App\Jobs\SendOverdueTaskNotificationJob;
use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Console\Command;

class DispatchOverdueTaskNotifications extends Command
{
    protected $signature = 'tasks:dispatch-overdue-notifications';

    protected $description =
        'Dispatch queued notifications for overdue tasks';

    public function handle(): int
    {
        Task::query()
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->where('status', '!=', TaskStatus::DONE->value)
            ->whereNull('overdue_notified_at')
            ->whereHas('project.user')
            ->select('id')
            ->chunkById(
                200,
                function ($tasks): void {
                    foreach ($tasks as $task) {
                        SendOverdueTaskNotificationJob::dispatch(
                            $task->id
                        );
                    }
                }
            );

        $this->info(
            'Overdue task notification jobs were dispatched.'
        );

        return self::SUCCESS;
    }
}