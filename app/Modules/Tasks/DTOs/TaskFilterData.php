<?php

namespace App\Modules\Tasks\DTOs;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;

final readonly class TaskFilterData
{
    public function __construct(
        public int $userId,
        public ?int $projectId,
        public ?TaskStatus $status,
        public ?TaskPriority $priority,
        public ?string $search,
        public int $perPage,
        public int $page,
    ) {
    }

    public static function fromArray(
        int $userId,
        array $data
    ): self {
        return new self(
            userId: $userId,

            projectId: isset($data['project_id'])
                ? (int) $data['project_id']
                : null,

            status: isset($data['status'])
                ? TaskStatus::from((int) $data['status'])
                : null,

            priority: isset($data['priority'])
                ? TaskPriority::from((int) $data['priority'])
                : null,

            search: isset($data['search'])
                ? trim($data['search'])
                : null,

            perPage: (int) ($data['per_page'] ?? 15),

            page: (int) ($data['page'] ?? 1),
        );
    }
}