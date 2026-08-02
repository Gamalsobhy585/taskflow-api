<?php

namespace Tests\Feature\Modules\Tasks;

use App\Enums\TaskStatus;
use App\Jobs\SendOverdueTaskNotificationJob;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Notifications\OverdueTaskNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class OverdueTaskNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_sends_notification_for_overdue_task(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::TODO,
            'due_date' => now()->subHour(),
            'overdue_notified_at' => null,
        ]);

        SendOverdueTaskNotificationJob::dispatchSync(
            $task->id
        );

        Notification::assertSentTo(
            $user,
            OverdueTaskNotification::class
        );

        $this->assertNotNull(
            $task->fresh()->overdue_notified_at
        );
    }

    public function test_job_does_not_notify_completed_task(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::DONE,
            'due_date' => now()->subDay(),
            'overdue_notified_at' => null,
        ]);

        SendOverdueTaskNotificationJob::dispatchSync(
            $task->id
        );

        Notification::assertNothingSent();

        $this->assertNull(
            $task->fresh()->overdue_notified_at
        );
    }

    public function test_same_overdue_task_is_not_notified_twice(): void
    {
        Notification::fake();

        $user = User::factory()->create();

        $project = Project::factory()->create([
            'user_id' => $user->id,
        ]);

        $task = Task::factory()->create([
            'project_id' => $project->id,
            'status' => TaskStatus::TODO,
            'due_date' => now()->subDay(),
            'overdue_notified_at' => now()->subHour(),
        ]);

        SendOverdueTaskNotificationJob::dispatchSync(
            $task->id
        );

        Notification::assertNothingSent();
    }
}