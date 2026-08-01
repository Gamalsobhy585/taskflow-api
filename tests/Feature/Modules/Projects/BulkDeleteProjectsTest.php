<?php

namespace Tests\Feature\Modules\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Concerns\FlushesRedisCache;
class BulkDeleteProjectsTest extends TestCase
{
    use RefreshDatabase;
    use  FlushesRedisCache;

    public function test_user_can_soft_delete_multiple_own_projects(): void
    {
        $permission = Permission::findOrCreate(
            'delete-project',
            'web'
        );

        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        $projects = Project::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            route('projects.bulk-destroy'),
            [
                'project_ids' => $projects->pluck('id')->all(),
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.deleted_count', 3);

        foreach ($projects as $project) {
            $this->assertSoftDeleted('projects', [
                'id' => $project->id,
            ]);
        }
    }

    public function test_bulk_delete_does_not_delete_another_users_project(): void
    {
        $permission = Permission::findOrCreate(
            'delete-project',
            'web'
        );

        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->givePermissionTo($permission);

        $ownProject = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $otherProject = Project::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            route('projects.bulk-destroy'),
            [
                'project_ids' => [
                    $ownProject->id,
                    $otherProject->id,
                ],
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.deleted_count', 1);

        $this->assertSoftDeleted('projects', [
            'id' => $ownProject->id,
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $otherProject->id,
            'deleted_at' => null,
        ]);
    }
    public function test_bulk_delete_invalidates_cached_project_list(): void
    {
        $user = User::factory()->create();

        $listPermission = Permission::findOrCreate(
            'list-project',
            'web'
        );

        $deletePermission = Permission::findOrCreate(
            'delete-project',
            'web'
        );

        $user->givePermissionTo(
            $listPermission,
            $deletePermission
        );

        $projects = Project::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        Sanctum::actingAs($user);

        $this->getJson(
            route('projects.index')
        )
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $this->deleteJson(
            route('projects.bulk-destroy'),
            [
                'project_ids' => $projects
                    ->pluck('id')
                    ->all(),
            ]
        )
            ->assertOk()
            ->assertJsonPath(
                'data.deleted_count',
                3
            );

        $this->getJson(
            route('projects.index')
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }
}