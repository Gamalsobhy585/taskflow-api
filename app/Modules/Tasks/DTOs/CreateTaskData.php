<?php

namespace App\Modules\Tasks\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Carbon\CarbonImmutable;

final readonly class CreateTaskData
{
    public function __construct(
        public int $projectId,
        public string $title,
        public ?string $description,
        public TaskPriority $priority,
        public TaskStatus $status,
        public ?CarbonImmutable $dueDate,
    ) {
    }

    public static function fromArray(
        int $projectId,
        array $data
    ): self {
        return new self(
            projectId: $projectId,
            title: $data['title'],
            description: $data['description'] ?? null,
            priority: TaskPriority::from(
                (int) $data['priority']
            ),
            status: TaskStatus::from(
                (int) ($data['status'] ?? TaskStatus::TODO->value)
            ),
            dueDate: isset($data['due_date'])
                ? CarbonImmutable::parse($data['due_date'])
                : null,
        );
    }

    public function toArray(): array
    {
        return [
            'project_id' => $this->projectId,
            'title' => $this->title,
            'description' => $this->description,
            'priority' => $this->priority->value,
            'status' => $this->status->value,
            'due_date' => $this->dueDate,
        ];
    }
}