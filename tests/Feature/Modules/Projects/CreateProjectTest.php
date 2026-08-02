<?php

namespace Tests\Feature\Modules\Projects;

use App\Enums\ProjectStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Tests\Concerns\FlushesRedisCache;
class CreateProjectTest extends TestCase
{
    use RefreshDatabase;
    use FlushesRedisCache;
    public function test_user_with_permission_can_create_project(): void
    {
        $permission = Permission::findOrCreate(
            'create-project',
            'web'
        );

        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('projects.store'),
            [
                'name' => 'Electro PI Assessment',
                'description' => 'Assessment project',
                'status' => ProjectStatus::ACTIVE->value,
            ]
        );

        $response
            ->assertCreated()
            ->assertJsonPath(
                'data.name',
                'Electro PI Assessment'
            )
            ->assertJsonPath(
                'data.status.value',
                ProjectStatus::ACTIVE->value
            )
            ->assertJsonPath(
                'data.status.label',
                'Active'
            );

        $this->assertDatabaseHas('projects', [
            'user_id' => $user->id,
            'name' => 'Electro PI Assessment',
            'status' => ProjectStatus::ACTIVE->value,
        ]);
    }

    public function test_user_without_permission_cannot_create_project(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('projects.store'),
            [
                'name' => 'Forbidden Project',
                'status' => ProjectStatus::ACTIVE->value,
            ]
        );

        $response->assertForbidden();

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_project_status_must_be_valid_enum_value(): void
    {
        $permission = Permission::findOrCreate(
            'create-project',
            'web'
        );

        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        $response = $this->postJson(
            route('projects.store'),
            [
                'name' => 'Invalid Status Project',
                'status' => 99,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');
    }
    public function test_creating_project_invalidates_cached_project_list(): void
    {
        $user = User::factory()->create();

        $createPermission = Permission::findOrCreate(
            'create-project',
            'web'
        );

        $listPermission = Permission::findOrCreate(
            'list-project',
            'web'
        );

        $user->givePermissionTo(
            $createPermission,
            $listPermission
        );

        Sanctum::actingAs($user);

        $this->getJson(
            route('projects.index')
        )
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->postJson(
            route('projects.store'),
            [
                'name' => 'Redis Project',
                'description' => 'Redis cache invalidation test',
                'status' => ProjectStatus::ACTIVE->value,
            ]
        )->assertCreated();

        $this->getJson(
            route('projects.index')
        )
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.name',
                'Redis Project'
            );
    }
}