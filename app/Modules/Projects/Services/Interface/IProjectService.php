<?php

namespace App\Modules\Projects\Services\Interface;

use App\Models\User;
use App\Modules\Projects\DTOs\BulkDeleteProjectsData;
use App\Modules\Projects\DTOs\CreateProjectData;
use App\Modules\Projects\DTOs\UpdateProjectData;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Project;

interface IProjectService
{
    public function create(
        CreateProjectData $data
    ): Project;

    public function list(
        User $user,
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator;

    public function find(
        int $projectId,
        User $user
    ): Project;

    public function update(
        int $projectId,
        User $user,
        UpdateProjectData $data
    ): Project;

    public function delete(
        int $projectId,
        User $user
    ): void;

    public function bulkDelete(
        BulkDeleteProjectsData $data
    ): int;
}