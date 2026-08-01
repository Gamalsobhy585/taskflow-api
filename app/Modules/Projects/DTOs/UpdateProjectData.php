<?php

namespace App\Modules\Projects\DTOs;

use App\Enums\ProjectStatus;

final readonly class UpdateProjectData
{
    public function __construct(
        public string $name,
        public ?string $description,
        public ProjectStatus $status,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
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
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status->value,
        ];
    }
}