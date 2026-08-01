<?php

namespace App\Modules\Projects\DTOs;

use App\Enums\ProjectStatus;

final readonly class CreateProjectData
{
    public function __construct(
        public int $userId,
        public string $name,
        public ?string $description,
        public ProjectStatus $status,
    ) {
    }

    public static function fromArray(
        int $userId,
        array $data
    ): self {
        return new self(
            userId: $userId,
            name: $data['name'],
            description: $data['description'] ?? null,
            status: ProjectStatus::from(
                (int) $data['status']
            ),
        );
    }

    public function toArray(): array
    {
        return [
            'user_id' => $this->userId,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status->value,
        ];
    }
}