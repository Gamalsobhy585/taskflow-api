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
class ListProjectTest extends TestCase
{
    use RefreshDatabase;
    use FlushesRedisCache;

    private function authenticateUserWithPermission(): User
    {
        $user = User::factory()->create();

        $permission = Permission::findOrCreate(
            'list-project',
            'web'
        );

        $user->givePermissionTo($permission);

        Sanctum::actingAs($user);

        return $user;
    }

    public function test_user_can_list_only_own_projects(): void
    {
        $user = $this->authenticateUserWithPermission();

        $otherUser = User::factory()->create();

        Project::factory()
            ->count(3)
            ->create([
                'user_id' => $user->id,
            ]);

        Project::factory()
            ->count(2)
            ->create([
                'user_id' => $otherUser->id,
            ]);

        $response = $this->getJson(
            route('projects.index')
        );

        $response
            ->assertOk()
            ->assertJsonCount(3, 'data');

        $returnedUserIds = collect(
            $response->json('data')
        )->pluck('user.id');

        $this->assertTrue(
            $returnedUserIds->every(
                fn ($userId): bool => $userId === $user->id
            )
        );
    }

    public function test_projects_are_paginated(): void
    {
        $user = $this->authenticateUserWithPermission();

        Project::factory()
            ->count(20)
            ->create([
                'user_id' => $user->id,
            ]);

        $response = $this->getJson(
            route('projects.index', [
                'per_page' => 5,
            ])
        );

        $response
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.per_page', 5)
            ->assertJsonPath('meta.total', 20);
    }

    public function test_soft_deleted_projects_are_not_returned(): void
    {
        $user = $this->authenticateUserWithPermission();

        $activeProject = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Visible Project',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $deletedProject = Project::factory()->create([
            'user_id' => $user->id,
            'name' => 'Deleted Project',
            'status' => ProjectStatus::ARCHIVED,
        ]);

        $deletedProject->delete();

        $response = $this->getJson(
            route('projects.index')
        );

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath(
                'data.0.id',
                $activeProject->id
            );

        $returnedIds = collect(
            $response->json('data')
        )->pluck('id');

        $this->assertFalse(
            $returnedIds->contains($deletedProject->id)
        );
    }

    public function test_user_without_permission_cannot_list_projects(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('projects.index')
        );

        $response->assertForbidden();
    }

    public function test_guest_cannot_list_projects(): void
    {
        $response = $this->getJson(
            route('projects.index')
        );

        $response->assertUnauthorized();
    }
    public function test_each_pagination_page_has_separate_cache_entry(): void
    {
        $user = $this->authenticateUserWithPermission();

        $projects = Project::factory()
            ->count(12)
            ->create([
                'user_id' => $user->id,
            ]);

        $firstPageResponse = $this->getJson(
            route('projects.index', [
                'page' => 1,
                'per_page' => 5,
            ])
        );

        $secondPageResponse = $this->getJson(
            route('projects.index', [
                'page' => 2,
                'per_page' => 5,
            ])
        );

        $firstPageResponse
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 1);

        $secondPageResponse
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonPath('meta.current_page', 2);

        $firstPageIds = collect(
            $firstPageResponse->json('data')
        )->pluck('id');

        $secondPageIds = collect(
            $secondPageResponse->json('data')
        )->pluck('id');

        $this->assertEmpty(
            $firstPageIds->intersect($secondPageIds)
        );
    }

}