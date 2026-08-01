<?php

namespace Database\Factories;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'priority' => fake()->randomElement(
                TaskPriority::cases()
            ),
            'status' => fake()->randomElement(
                TaskStatus::cases()
            ),
            'due_date' => fake()->dateTimeBetween(
                '-5 days',
                '+10 days'
            ),
            'overdue_notified_at' => null,
        ];
    }
}