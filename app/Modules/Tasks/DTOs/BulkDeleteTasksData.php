<?php

namespace App\Modules\Tasks\DTOs;

final readonly class BulkDeleteTasksData
{
    /**
     * @param array<int, int> $taskIds
     */
    public function __construct(
        public int $userId,
        public array $taskIds,
    ) {
    }

    public static function fromArray(
        int $userId,
        array $data
    ): self {
        return new self(
            userId: $userId,
            taskIds: array_map(
                'intval',
                $data['task_ids']
            ),
        );
    }
}