<?php

namespace App\Modules\Dashboard\Repositories\Implementation;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Modules\Dashboard\DTOs\DashboardStatisticsData;
use App\Modules\Dashboard\Repositories\Interface\IDashboardRepository;

class DashboardRepository implements IDashboardRepository
{
    public function getStatisticsForUser(
        int $userId
    ): DashboardStatisticsData {
        $projectStatistics = Project::query()
            ->where('user_id', $userId)
            ->selectRaw('COUNT(*) as total_projects')
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active_projects',
                [ProjectStatus::ACTIVE->value]
            )
            ->first();

        $taskStatistics = Task::query()
            ->whereHas(
                'project',
                fn ($query) => $query->where(
                    'user_id',
                    $userId
                )
            )
            ->selectRaw('COUNT(*) as total_tasks')
            ->selectRaw(
                'SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_tasks',
                [TaskStatus::DONE->value]
            )
            ->selectRaw(
                'SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END) as pending_tasks',
                [
                    TaskStatus::TODO->value,
                    TaskStatus::IN_PROGRESS->value,
                ]
            )
            ->selectRaw(
                '
                SUM(
                    CASE
                        WHEN due_date IS NOT NULL
                        AND due_date < ?
                        AND status != ?
                        THEN 1
                        ELSE 0
                    END
                ) as overdue_tasks
                ',
                [
                    now(),
                    TaskStatus::DONE->value,
                ]
            )
            ->first();

        return DashboardStatisticsData::fromArray([
            'total_projects' =>
                $projectStatistics?->total_projects ?? 0,

            'active_projects' =>
                $projectStatistics?->active_projects ?? 0,

            'total_tasks' =>
                $taskStatistics?->total_tasks ?? 0,

            'completed_tasks' =>
                $taskStatistics?->completed_tasks ?? 0,

            'pending_tasks' =>
                $taskStatistics?->pending_tasks ?? 0,

            'overdue_tasks' =>
                $taskStatistics?->overdue_tasks ?? 0,
        ]);
    }
}