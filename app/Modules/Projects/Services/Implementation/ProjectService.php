<?php

namespace App\Modules\Projects\Services\Implementation;

use App\Models\Project;
use App\Models\User;
use App\Modules\Projects\Cache\Interface\IProjectCache;
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
        private readonly IProjectRepository $projectRepository,
        private readonly IProjectCache $projectCache
    ) {
    }

    public function create(
        CreateProjectData $data
    ): Project {
        $project = DB::transaction(
            fn (): Project => $this->projectRepository->create($data)
        );

        /*
         * The cached project lists are now outdated.
         * Cache invalidation happens after the transaction succeeds.
         */
        $this->projectCache->flushForUser($data->userId);

        return $project;
    }

    public function list(
        User $user,
        int $perPage = 15,
        int $page = 1
    ): LengthAwarePaginator {
        return $this->projectCache->rememberList(
            userId: $user->id,
            page: $page,
            perPage: $perPage,
            callback: fn (): LengthAwarePaginator =>
                $this->projectRepository->paginateForUser(
                    userId: $user->id,
                    perPage: $perPage,
                    page: $page
                )
        );
    }

    public function find(
        int $projectId,
        User $user
    ): Project {
        $project = $this->projectCache->rememberProject(
            userId: $user->id,
            projectId: $projectId,
            callback: fn (): ?Project =>
                $this->projectRepository->findForUser(
                    projectId: $projectId,
                    userId: $user->id
                )
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

        $updatedProject = DB::transaction(
            fn (): Project => $this->projectRepository->update(
                $project,
                $data
            )
        );

        /*
         * Remove:
         * - cached project item
         * - all cached pagination pages
         */
        $this->projectCache->flushForUser($user->id);

        return $updatedProject;
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

        $this->projectCache->flushForUser($user->id);
    }

    public function bulkDelete(
        BulkDeleteProjectsData $data
    ): int {
        $deletedCount = DB::transaction(
            fn (): int => $this->projectRepository
                ->bulkDeleteForUser(
                    userId: $data->userId,
                    projectIds: $data->projectIds
                )
        );

        if ($deletedCount > 0) {
            $this->projectCache->flushForUser($data->userId);
        }

        return $deletedCount;
    }
}