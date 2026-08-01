<?php

namespace Tests\Feature\Modules\Tasks;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\Concerns\MocksTaskCache;
use Tests\TestCase;

class BulkDeleteTasksTest extends TestCase
{
    use RefreshDatabase;
    use MocksTaskCache;
    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskCache();
    }

    public function test_user_can_bulk_delete_owned_tasks(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $tasks = Task::factory()
            ->count(3)
            ->create([
                'project_id' => $project->id,
            ]);

        $user->givePermissionTo(
            Permission::findOrCreate('delete-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            route('tasks.bulk-destroy'),
            [
                'task_ids' => $tasks->pluck('id')->all(),
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.deleted_count',
                3
            );

        foreach ($tasks as $task) {
            $this->assertSoftDeleted('tasks', [
                'id' => $task->id,
            ]);
        }
    }

    public function test_bulk_delete_does_not_delete_another_users_tasks(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownProject = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProject = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $ownTask = Task::factory()->create([
            'project_id' => $ownProject->id,
        ]);

        $otherTask = Task::factory()->create([
            'project_id' => $otherProject->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('delete-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            route('tasks.bulk-destroy'),
            [
                'task_ids' => [
                    $ownTask->id,
                    $otherTask->id,
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.deleted_count',
                1
            );

        $this->assertSoftDeleted('tasks', [
            'id' => $ownTask->id,
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $otherTask->id,
            'deleted_at' => null,
        ]);
    }

    public function test_bulk_delete_requires_task_ids(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo(
            Permission::findOrCreate('delete-task', 'web')
        );

        Sanctum::actingAs($user);

        $this->deleteJson(
            route('tasks.bulk-destroy'),
            [
                'task_ids' => [],
            ]
        )
            ->assertUnprocessable()
            ->assertJsonValidationErrors(
                'task_ids'
            );
    }

    public function test_user_without_permission_cannot_bulk_delete_tasks(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->deleteJson(
            route('tasks.bulk-destroy'),
            [
                'task_ids' => [1],
            ]
        )->assertForbidden();
    }
}