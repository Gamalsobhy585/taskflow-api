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

class ViewTaskTest extends TestCase
{
    use RefreshDatabase;
    use MocksTaskCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskCache();
    }

    public function test_user_can_view_task_from_own_project(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('view-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('tasks.show', $task->id)
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $task->id)
            ->assertJsonPath('data.title', $task->title);
    }

    public function test_user_cannot_view_another_users_task(): void
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
            Permission::findOrCreate('view-task', 'web')
        );

        Sanctum::actingAs($user);

        $this->getJson(
            route('tasks.show', $task->id)
        )->assertNotFound();
    }

    public function test_user_without_permission_cannot_view_task(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
        ]);

        Sanctum::actingAs($user);

        $this->getJson(
            route('tasks.show', $task->id)
        )->assertForbidden();
    }

    public function test_guest_cannot_view_task(): void
    {
        $task = Task::factory()->create();

        $this->getJson(
            route('tasks.show', $task->id)
        )->assertUnauthorized();
    }
}