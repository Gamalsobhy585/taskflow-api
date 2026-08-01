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

class ListTasksTest extends TestCase
{
    use RefreshDatabase;
    use MocksTaskCache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockTaskCache();
    }

    public function test_user_can_list_only_tasks_from_own_projects(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownProject = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProject = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Task::factory()->count(3)->create([
            'project_id' => $ownProject->id,
        ]);

        Task::factory()->count(2)->create([
            'project_id' => $otherProject->id,
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
            ->assertJsonCount(3, 'data');
    }

    public function test_task_list_is_paginated(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Task::factory()->count(12)->create([
            'project_id' => $project->id,
        ]);

        $user->givePermissionTo(
            Permission::findOrCreate('list-task', 'web')
        );

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('tasks.index', [
                'per_page' => 5,
                'page' => 1,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 12);
    }

    public function test_soft_deleted_tasks_are_not_returned(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $visibleTask = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Visible Task',
        ]);

        $deletedTask = Task::factory()->create([
            'project_id' => $project->id,
            'title' => 'Deleted Task',
        ]);

        $deletedTask->delete();

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
                'data.0.id',
                $visibleTask->id
            );
    }

    public function test_user_without_permission_cannot_list_tasks(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson(
            route('tasks.index')
        )->assertForbidden();
    }

    public function test_guest_cannot_list_tasks(): void
    {
        $this->getJson(
            route('tasks.index')
        )->assertUnauthorized();
    }
}