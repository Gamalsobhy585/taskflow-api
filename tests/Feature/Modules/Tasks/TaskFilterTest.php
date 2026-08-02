<?php

namespace Tests\Feature\Modules\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\MocksTaskCache;
use Tests\TestCase;

class TaskFilterTest extends TestCase
{
    use RefreshDatabase;
    use MocksTaskCache;
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskCache();
    }


    public function test_user_can_filter_tasks_by_status_priority_and_title(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('list-task', 'web')
        );

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Important backend assessment',
            'priority' => TaskPriority::HIGH,
            'status' => TaskStatus::IN_PROGRESS,
        ]);

        Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Write documentation',
            'priority' => TaskPriority::LOW,
            'status' => TaskStatus::TODO,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('tasks.index', [
                'status' => TaskStatus::IN_PROGRESS->value,
                'priority' => TaskPriority::HIGH->value,
                'search' => 'assessment',
            ])
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.title',
                'Important backend assessment'
            );
    }

    public function test_list_does_not_return_another_users_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownProject = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProject = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Task::factory()->create([
            'project_id' => $ownProject->id,
            'title' => 'Own task',
        ]);

        Task::factory()->create([
            'project_id' => $otherProject->id,
            'title' => 'Other user task',
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('list-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('tasks.index')
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.title',
                'Own task'
            );
    }
}