<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OverdueTaskNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Task $task
    ) {
    }

    public function via(object $notifiable): array
    {
        return [
            'mail',
            'database',
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('Overdue task: ' . $this->task->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line(
                'The following task is overdue: '
                . $this->task->title
            )
            ->line(
                'Project: '
                . $this->task->project->name
            )
            ->line(
                'Due date: '
                . $this->task->due_date?->format('Y-m-d H:i')
            );
    }

    public function toArray(object $notifiable): array
    {
        return [
            'task_id' => $this->task->id,
            'project_id' => $this->task->project_id,
            'task_title' => $this->task->title,
            'project_name' => $this->task->project->name,
            'due_date' => $this->task->due_date?->toISOString(),
            'message' => 'The task is overdue.',
        ];
    }
}