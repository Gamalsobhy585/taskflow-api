<?php

namespace Tests\Feature\Modules\Tasks;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\MocksTaskCache;
use Tests\TestCase;

class CreateTaskTest extends TestCase
{
    use RefreshDatabase;
    use MocksTaskCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskCache();
    }

    public function test_user_can_create_task_for_owned_project(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('create-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('tasks.store'),
            [
                'project_id' => $project->id,
                'title' => 'Create Tasks Module',
                'description' => 'Implement the task APIs.',
                'priority' => TaskPriority::HIGH->value,
                'status' => TaskStatus::TODO->value,
                'due_date' => now()->addDays(2)
                    ->toDateTimeString(),
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.title',
                'Create Tasks Module'
            )
            ->assertJsonPath(
                'data.priority.value',
                TaskPriority::HIGH->value
            )
            ->assertJsonPath(
                'data.status.value',
                TaskStatus::TODO->value
            );

        $this->assertDatabaseHas('tasks', [
            'project_id' => $project->id,
            'title' => 'Create Tasks Module',
            'priority' => TaskPriority::HIGH->value,
            'status' => TaskStatus::TODO->value,
        ]);
    }

    public function test_user_cannot_create_task_for_another_users_project(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('create-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('tasks.store'),
            [
                'project_id' => $project->id,
                'title' => 'Unauthorized Task',
                'priority' => TaskPriority::HIGH->value,
                'status' => TaskStatus::TODO->value,
            ]
        );

        $response->assertNotFound();

        $this->assertDatabaseCount('tasks', 0);
    }

    public function test_user_without_permission_cannot_create_task(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('tasks.store'),
            [
                'project_id' => $project->id,
                'title' => 'Forbidden Task',
                'priority' => TaskPriority::LOW->value,
                'status' => TaskStatus::TODO->value,
            ]
        );

        $response->assertForbidden();
    }

    public function test_task_creation_requires_valid_data(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo(
            Permission::findOrCreate('create-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('tasks.store'),
            [
                'project_id' => 999999,
                'title' => '',
                'priority' => 99,
                'status' => 99,
                'due_date' => 'invalid-date',
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'project_id',
                'title',
                'priority',
                'status',
                'due_date',
            ]);
    }
}