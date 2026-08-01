<?php

namespace Tests\Feature\Modules\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Concerns\FlushesRedisCache;

class UpdateProjectTest extends TestCase
{
    use RefreshDatabase;
    use FlushesRedisCache;

    private function authenticateUserWithPermission(): User
    {
        $user = User::factory()->create();

        $permission = Permission::findOrCreate(
            'update-project',
            'web'
        );

        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        return $user;
    }

    public function test_user_can_update_own_project(): void
    {
        $user = $this->authenticateUserWithPermission();

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Project Name',
            'description' => 'Old description',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->putJson(
            route('projects.update', $project->id),
            [
                'name' => 'Updated Project Name',
                'description' => 'Updated description',
                'status' => ProjectStatus::COMPLETED->value,
            ]
        );

        $response
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'Updated Project Name'
            )
            ->assertJsonPath(
                'data.description',
                'Updated description'
            )
            ->assertJsonPath(
                'data.status.value',
                ProjectStatus::COMPLETED->value
            )
            ->assertJsonPath(
                'data.status.label',
                ProjectStatus::COMPLETED->label()
            );

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $user->id,
            'name' => 'Updated Project Name',
            'description' => 'Updated description',
            'status' => ProjectStatus::COMPLETED->value,
        ]);
    }

    public function test_user_cannot_update_another_users_project(): void
    {
        $user = $this->authenticateUserWithPermission();

        $owner = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $owner->id,
            'name' => 'Owner Project',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $response = $this->putJson(
            route('projects.update', $project->id),
            [
                'name' => 'Unauthorized Update',
                'description' => 'Should not be saved',
                'status' => ProjectStatus::COMPLETED->value,
            ]
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'user_id' => $owner->id,
            'name' => 'Owner Project',
            'status' => ProjectStatus::ACTIVE->value,
        ]);

        $this->assertDatabaseMissing('projects', [
            'id' => $project->id,
            'name' => 'Unauthorized Update',
        ]);
    }

    public function test_user_without_permission_cannot_update_project(): void
    {
        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson(
            route('projects.update', $project->id),
            [
                'name' => 'Updated Project',
                'description' => null,
                'status' => ProjectStatus::COMPLETED->value,
            ]
        );

        $response->assertForbidden();
    }

    public function test_update_project_requires_valid_data(): void
    {
        $user = $this->authenticateUserWithPermission();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->putJson(
            route('projects.update', $project->id),
            [
                'name' => '',
                'description' => [],
                'status' => 99,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'description',
                'status',
            ]);
    }

    public function test_guest_cannot_update_project(): void
    {
        $project = Project::factory()->create();

        $response = $this->putJson(
            route('projects.update', $project->id),
            [
                'name' => 'Updated Project',
                'description' => null,
                'status' => ProjectStatus::ACTIVE->value,
            ]
        );

        $response->assertUnauthorized();
    }
    public function test_updating_project_invalidates_cached_project(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo([
            Permission::findOrCreate(
                'view-project',
                'web'
            ),
            Permission::findOrCreate(
                'update-project',
                'web'
            ),
        ]);

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Old Cached Name',
            'description' => 'Old description',
            'status' => ProjectStatus::ACTIVE,
        ]);

        Sanctum::actingAs($user);

        /*
        * Cache the project with the old name.
        */
        $this->getJson(
            route('projects.show', $project->id)
        )
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'Old Cached Name'
            );

        $this->putJson(
            route('projects.update', $project->id),
            [
                'name' => 'New Updated Name',
                'description' => 'New description',
                'status' => ProjectStatus::COMPLETED->value,
            ]
        )->assertOk();

        /*
        * Must return the new data, not the old cached model.
        */
        $this->getJson(
            route('projects.show', $project->id)
        )
            ->assertOk()
            ->assertJsonPath(
                'data.name',
                'New Updated Name'
            )
            ->assertJsonPath(
                'data.description',
                'New description'
            )
            ->assertJsonPath(
                'data.status.value',
                ProjectStatus::COMPLETED->value
            );
    }

}