<?php

namespace Tests\Feature\Modules\Dashboard;

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_own_dashboard_statistics(): void
    {
        $user = User::factory()->create();

        $user->givePermissionTo(
            Permission::findOrCreate(
                'view-dashboard',
                'web'
            )
        );

        $activeProject = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $completedProject = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::COMPLETED,
        ]);

        Task::factory()->create([
            'project_id' => $activeProject->id,
            'status' => TaskStatus::DONE,
            'due_date' => now()->subDay(),
        ]);

        Task::factory()->create([
            'project_id' => $activeProject->id,
            'status' => TaskStatus::TODO,
            'due_date' => now()->subDay(),
        ]);

        Task::factory()->create([
            'project_id' => $completedProject->id,
            'status' => TaskStatus::IN_PROGRESS,
            'due_date' => now()->addDay(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('dashboard.statistics')
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.total_projects', 2)
            ->assertJsonPath('data.active_projects', 1)
            ->assertJsonPath('data.total_tasks', 3)
            ->assertJsonPath('data.completed_tasks', 1)
            ->assertJsonPath('data.pending_tasks', 2)
            ->assertJsonPath('data.overdue_tasks', 1);
    }

    public function test_dashboard_does_not_include_other_users_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $user->givePermissionTo(
            Permission::findOrCreate(
                'view-dashboard',
                'web'
            )
        );

        $ownProject = Project::factory()->create([
            'user_id' => $user->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        $otherProject = Project::factory()->create([
            'user_id' => $otherUser->id,
            'status' => ProjectStatus::ACTIVE,
        ]);

        Task::factory()->create([
            'project_id' => $ownProject->id,
        ]);

        Task::factory()->count(5)->create([
            'project_id' => $otherProject->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson(
            route('dashboard.statistics')
        );

        $response
            ->assertOk()
            ->assertJsonPath('data.total_projects', 1)
            ->assertJsonPath('data.total_tasks', 1);
    }

    public function test_user_without_permission_cannot_view_dashboard(): void
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $this->getJson(
            route('dashboard.statistics')
        )->assertForbidden();
    }

    public function test_guest_cannot_view_dashboard(): void
    {
        $this->getJson(
            route('dashboard.statistics')
        )->assertUnauthorized();
    }
}