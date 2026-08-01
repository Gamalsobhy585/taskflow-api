<?php

namespace App\Modules\Projects\Repositories\Implementation;

use App\Models\Project;
use App\Modules\Projects\DTOs\CreateProjectData;
use App\Modules\Projects\DTOs\UpdateProjectData;
use App\Modules\Projects\Repositories\Interface\IProjectRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectRepository implements IProjectRepository
{
    public function create(
        CreateProjectData $data
    ): Project {
        return Project::query()->create(
            $data->toArray()
        );
    }

    public function paginateForUser(
        int $userId,
        int $perPage = 15
    ): LengthAwarePaginator {
        return Project::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->paginate($perPage);
    }

    public function findForUser(
        int $projectId,
        int $userId
    ): ?Project {
        return Project::query()
            ->whereKey($projectId)
            ->where('user_id', $userId)
            ->first();
    }

    public function update(
        Project $project,
        UpdateProjectData $data
    ): Project {
        $project->update(
            $data->toArray()
        );

        return $project->refresh();
    }

    public function delete(Project $project): bool
    {
        return (bool) $project->delete();
    }

    public function bulkDeleteForUser(
        int $userId,
        array $projectIds
    ): int {
        return Project::query()
            ->where('user_id', $userId)
            ->whereIn('id', $projectIds)
            ->delete();
    }
}