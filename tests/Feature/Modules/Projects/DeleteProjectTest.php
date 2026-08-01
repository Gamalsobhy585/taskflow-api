<?php

namespace Tests\Feature\Modules\Projects;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Concerns\FlushesRedisCache;

class DeleteProjectTest extends TestCase
{
    use RefreshDatabase;
    use FlushesRedisCache;


    private function authenticateUserWithPermission(): User
    {
        $user = User::factory()->create();

        $permission = Permission::findOrCreate(
            'delete-project',
            'web'
        );

        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        return $user;
    }

    public function test_user_can_soft_delete_own_project(): void
    {
        $user = $this->authenticateUserWithPermission();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->deleteJson(
            route('projects.destroy', $project->id)
        );

        $response->assertOk();

        $this->assertSoftDeleted('projects', [
            'id' => $project->id,
            'user_id' => $user->id,
        ]);

        $this->assertNotNull(
            $project->fresh()->deleted_at
        );
    }

    public function test_user_cannot_delete_another_users_project(): void
    {
        $user = $this->authenticateUserWithPermission();

        $owner = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->deleteJson(
            route('projects.destroy', $project->id)
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $owner->id,
            'deleted_at' => null,
        ]);
    }

    public function test_user_without_permission_cannot_delete_project(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->deleteJson(
            route('projects.destroy', $project->id)
        );

        $response->assertForbidden();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'deleted_at' => null,
        ]);
    }

    public function test_deleting_non_existing_project_returns_not_found(): void
    {
        $this->authenticateUserWithPermission();

        $response = $this->deleteJson(
            route('projects.destroy', 999999)
        );

        $response->assertNotFound();
    }

    public function test_guest_cannot_delete_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->deleteJson(
            route('projects.destroy', $project->id)
        );

        $response->assertUnauthorized();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'deleted_at' => null,
        ]);
    }
    public function test_deleting_project_invalidates_cached_project(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo([
            Permission::findOrCreate(
                'view-project',
                'web'
            ),
            Permission::findOrCreate(
                'delete-project',
                'web'
            ),
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        /*
        * Store the project in Redis.
        */
        $this->getJson(
            route('projects.show', $project->id)
        )->assertOk();

        /*
        * Delete it and clear its user cache.
        */
        $this->deleteJson(
            route('projects.destroy', $project->id)
        )->assertOk();

        /*
        * Redis must not return the deleted project.
        */
        $this->getJson(
            route('projects.show', $project->id)
        )->assertNotFound();
    }
}