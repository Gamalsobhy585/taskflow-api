<?php

namespace App\Modules\Projects\Repositories\Interface;

use App\Models\Project;
use App\Modules\Projects\DTOs\CreateProjectData;
use App\Modules\Projects\DTOs\UpdateProjectData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface IProjectRepository
{
    public function create(
        CreateProjectData $data
    ): Project;

    public function paginateForUser(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator;

    public function findForUser(
        int $projectId,
        int $userId
    ): ?Project;

    public function update(
        Project $project,
        UpdateProjectData $data
    ): Project;

    public function delete(Project $project): bool;

    /**
     * @param array<int, int> $projectIds
     */
    public function bulkDeleteForUser(
        int $userId,
        array $projectIds
    ): int;
}