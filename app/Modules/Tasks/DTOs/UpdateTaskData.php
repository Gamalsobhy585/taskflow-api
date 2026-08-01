<?php

namespace App\Modules\Tasks\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\CarbonImmutable;

final readonly class UpdateTaskData
{
    public function __construct(
        public string $title,
        public ?string $description,
        public TaskPriority $priority,
        public TaskStatus $status,
        public ?CarbonImmutable $dueDate,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            description: $data['description'] ?? null,
            priority: TaskPriority::from(
                (int) $data['priority']
            ),
            status: TaskStatus::from(
                (int) $data['status']
            ),
            dueDate: isset($data['due_date'])
                ? CarbonImmutable::parse($data['due_date'])
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'due_date' => $this->dueDate,

            /*
             * If the due date or task status is changed,
             * allow a future overdue notification again.
             */
            'overdue_notified_at' => null,
        ];
    }
}