<?php

namespace App\Modules\Projects\Services\Implementation;

use App\Models\Project;
use App\Models\User;
use App\Modules\Projects\DTOs\BulkDeleteProjectsData;
use App\Modules\Projects\DTOs\CreateProjectData;
use App\Modules\Projects\DTOs\UpdateProjectData;
use App\Modules\Projects\Exceptions\ProjectDeletionFailedException;
use App\Modules\Projects\Exceptions\ProjectNotFoundException;
use App\Modules\Projects\Repositories\Interface\IProjectRepository;
use App\Modules\Projects\Services\Interface\IProjectService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectService implements IProjectService
{
    public function __construct(
        private readonly IProjectRepository $projectRepository
    ) {
    }

    public function create(
        CreateProjectData $data
    ): Project {
        return DB::transaction(
            fn (): Project => $this->projectRepository->create($data)
        );
    }

    public function list(
        User $user,
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->projectRepository->paginateForUser(
            userId: $user->id,
            perPage: $perPage
        );
    }

    public function find(
        int $projectId,
        User $user
    ): Project {
        $project = $this->projectRepository->findForUser(
            projectId: $projectId,
            userId: $user->id
        );

        if (!$project) {
            throw new ProjectNotFoundException();
        }

        return $project;
    }

    public function update(
        int $projectId,
        User $user,
        UpdateProjectData $data
    ): Project {
        $project = $this->find(
            projectId: $projectId,
            user: $user
        );

        return DB::transaction(
            fn (): Project => $this->projectRepository->update(
                $project,
                $data
            )
        );
    }

    public function delete(
        int $projectId,
        User $user
    ): void {
        $project = $this->find(
            projectId: $projectId,
            user: $user
        );

        $deleted = DB::transaction(
            fn (): bool => $this->projectRepository->delete($project)
        );

        if (!$deleted) {
            throw new ProjectDeletionFailedException();
        }
    }

    public function bulkDelete(
        BulkDeleteProjectsData $data
    ): int {
        return DB::transaction(
            fn (): int => $this->projectRepository
                ->bulkDeleteForUser(
                    userId: $data->userId,
                    projectIds: $data->projectIds
                )
        );
    }
}