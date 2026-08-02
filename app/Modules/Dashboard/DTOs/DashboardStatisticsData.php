<?php

namespace App\Modules\Dashboard\DTOs;

final readonly class DashboardStatisticsData
{
    public function __construct(
        public int $totalProjects,
        public int $activeProjects,
        public int $totalTasks,
        public int $completedTasks,
        public int $pendingTasks,
        public int $overdueTasks,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            totalProjects: (int) $data['total_projects'],
            activeProjects: (int) $data['active_projects'],
            totalTasks: (int) $data['total_tasks'],
            completedTasks: (int) $data['completed_tasks'],
            pendingTasks: (int) $data['pending_tasks'],
            overdueTasks: (int) $data['overdue_tasks'],
        );
    }

    public function toArray(): array
    {
        return [
            'total_projects' => $this->totalProjects,
            'active_projects' => $this->activeProjects,
            'total_tasks' => $this->totalTasks,
            'completed_tasks' => $this->completedTasks,
            'pending_tasks' => $this->pendingTasks,
            'overdue_tasks' => $this->overdueTasks,
        ];
    }
}