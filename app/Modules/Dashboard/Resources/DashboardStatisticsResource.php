<?php

namespace App\Modules\Dashboard\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardStatisticsResource extends JsonResource
{
    public function toArray(Request $request): array
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