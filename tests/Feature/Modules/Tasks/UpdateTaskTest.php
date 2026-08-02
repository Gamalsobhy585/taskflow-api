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

class UpdateTaskTest extends TestCase
{
    use RefreshDatabase;
    use MocksTaskCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskCache();
    }

    public function test_user_can_update_task_from_own_project(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Old Title',
            'priority' => TaskPriority::LOW,
            'status' => TaskStatus::TODO,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('update-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->putJson(
            route('tasks.update', $task->id),
            [
                'title' => 'Updated Title',
                'description' => 'Updated description',
                'priority' => TaskPriority::HIGH->value,
                'status' => TaskStatus::IN_PROGRESS->value,
                'due_date' => now()->addDays(3)
                    ->toDateTimeString(),
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.title',
                'Updated Title'
            )
            ->assertJsonPath(
                'data.priority.value',
                TaskPriority::HIGH->value
            )
            ->assertJsonPath(
                'data.status.value',
                TaskStatus::IN_PROGRESS->value
            );

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Updated Title',
            'priority' => TaskPriority::HIGH->value,
            'status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }

    public function test_user_cannot_update_another_users_task(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Owner Task',
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('update-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->putJson(
            route('tasks.update', $task->id),
            [
                'title' => 'Unauthorized Update',
                'description' => null,
                'priority' => TaskPriority::HIGH->value,
                'status' => TaskStatus::DONE->value,
                'due_date' => null,
            ]
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Owner Task',
        ]);
    }

    public function test_task_update_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('update-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->putJson(
            route('tasks.update', $task->id),
            [
                'title' => '',
                'description' => [],
                'priority' => 99,
                'status' => 99,
                'due_date' => 'wrong-date',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'title',
                'description',
                'priority',
                'status',
                'due_date',
            ]);
    }

    public function test_user_without_permission_cannot_update_task(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->putJson(
            route('tasks.update', $task->id),
            [
                'title' => 'Updated Task',
                'description' => null,
                'priority' => TaskPriority::HIGH->value,
                'status' => TaskStatus::DONE->value,
                'due_date' => null,
            ]
        )->assertForbidden();
    }
}