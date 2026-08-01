<?php

namespace App\Modules\Projects\Seeders;

use App\Enums\ProjectStatus;
use App\Models\Project;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        Project::query()->create([
            'user_id' => 1,
            'name' => 'User One Active Project',
            'description' => 'First seeded project.',
            'status' => ProjectStatus::ACTIVE,
        ]);

        Project::query()->create([
            'user_id' => 1,
            'name' => 'User One Completed Project',
            'description' => 'Completed seeded project.',
            'status' => ProjectStatus::COMPLETED,
        ]);

        Project::query()->create([
            'user_id' => 2,
            'name' => 'User Two Archived Project',
            'description' => 'Archived seeded project.',
            'status' => ProjectStatus::ARCHIVED,
        ]);
    }
}