<?php

namespace Tests\Feature\Modules\Projects;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ViewProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_project(): void
    {
        $permission = Permission::findOrCreate(
            'view-project',
            'web'
        );

        $user = User::factory()->create();
        $user->givePermissionTo($permission);

        $project = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('projects.show', $project->id)
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $project->id);
    }

    public function test_user_cannot_view_another_users_project(): void
    {
        $permission = Permission::findOrCreate(
            'view-project',
            'web'
        );

        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $otherUser->givePermissionTo($permission);

        $project = Project::factory()->create([
            'user_id' => $owner->id,
        ]);

        Sanctum::actingAs($otherUser);

        $response = $this->getJson(
            route('projects.show', $project->id)
        );

        $response->assertNotFound();
    }
}