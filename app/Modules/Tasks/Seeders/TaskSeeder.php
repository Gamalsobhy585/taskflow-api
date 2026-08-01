<?php

namespace App\Modules\Tasks\Seeders;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $firstProject = Project::query()
            ->where('user_id', 1)
            ->first();

        $secondProject = Project::query()
            ->where('user_id', 2)
            ->first();

        if ($firstProject) {
            Task::query()->create([
                'project_id' => $firstProject->id,
                'title' => 'Create project API',
                'description' => 'Implement project creation endpoint.',
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::IN_PROGRESS,
                'due_date' => now()->addDays(2),
            ]);

            Task::query()->create([
                'project_id' => $firstProject->id,
                'title' => 'Write project tests',
                'description' => 'Create unit and feature tests.',
                'priority' => TaskPriority::MEDIUM,
                'status' => TaskStatus::TODO,
                'due_date' => now()->addDays(4),
            ]);
        }

        if ($secondProject) {
            Task::query()->create([
                'project_id' => $secondProject->id,
                'title' => 'Review assessment',
                'priority' => TaskPriority::LOW,
                'status' => TaskStatus::DONE,
                'due_date' => now()->subDay(),
            ]);
        }
    }
}