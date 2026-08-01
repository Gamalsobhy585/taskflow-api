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

class DeleteTaskTest extends TestCase
{
    use RefreshDatabase;
    use MocksTaskCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskCache();
    }

    public function test_user_can_soft_delete_owned_task(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('delete-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            route('tasks.destroy', $task->id)
        );

        $response->assertOk();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }

    public function test_user_cannot_delete_another_users_task(): void
    {
        $owner = User::factory()->create();
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('delete-task', 'web')
        );

        Sanctum::actingAs($user);

        $this->deleteJson(
            route('tasks.destroy', $task->id)
        )->assertNotFound();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_without_permission_cannot_delete_task(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->deleteJson(
            route('tasks.destroy', $task->id)
        )->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }

    public function test_guest_cannot_delete_task(): void
    {
        $task = Task::factory()->create();

        $this->deleteJson(
            route('tasks.destroy', $task->id)
        )->assertUnauthorized();
    }
}