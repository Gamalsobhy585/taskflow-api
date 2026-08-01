<?php

namespace App\Modules\Projects\DTOs;

final readonly class BulkDeleteProjectsData
{
    /**
     * @param array<int, int> $projectIds
     */
    public function __construct(
        public int $userId,
        public array $projectIds,
    ) {
    }

    public static function fromArray(
        int $userId,
        array $data
    ): self {
        return new self(
            userId: $userId,
            projectIds: array_map(
                'intval',
                $data['project_ids']
            ),
        );
    }
}