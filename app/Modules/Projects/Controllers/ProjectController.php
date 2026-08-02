<?php

namespace App\Modules\Projects\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Modules\Projects\DTOs\BulkDeleteProjectsData;
use App\Modules\Projects\DTOs\CreateProjectData;
use App\Modules\Projects\DTOs\UpdateProjectData;
use App\Modules\Projects\Requests\BulkDeleteProjectsRequest;
use App\Modules\Projects\Requests\StoreProjectRequest;
use App\Modules\Projects\Requests\UpdateProjectRequest;
use App\Modules\Projects\Resources\ProjectCollection;
use App\Modules\Projects\Resources\ProjectResource;
use App\Modules\Projects\Services\Interface\IProjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private readonly IProjectService $projectService
    ) {
    }

    public function index(Request $request): ProjectCollection
    {
        $perPage = min(
            max((int) $request->integer('per_page', 15), 1),
            100
        );

        $page = max(
            (int) $request->integer('page', 1),
            1
        );

        $projects = $this->projectService->list(
            user: $request->user(),
            perPage: $perPage,
            page: $page
        );

        return new ProjectCollection($projects);
    }

    public function store(
        StoreProjectRequest $request
    ): JsonResponse {
        $data = CreateProjectData::fromArray(
            userId: $request->user()->id,
            data: $request->validated()
        );

        $project = $this->projectService->create($data);

        return $this->returnData(
            __('messages.projects.created'),
            201,
            new ProjectResource($project)
        );
    }

    public function show(
        Request $request,
        int $project
    ): JsonResponse {
        abort_unless(
            $request->user()->can('view-project'),
            403
        );

        $projectModel = $this->projectService->find(
            projectId: $project,
            user: $request->user()
        );

        return $this->returnData(
            __('messages.projects.retrieved'),
            200,
            new ProjectResource($projectModel)
        );
    }

    public function update(
        UpdateProjectRequest $request,
        int $project
    ): JsonResponse {
        $data = UpdateProjectData::fromArray(
            $request->validated()
        );

        $updatedProject = $this->projectService->update(
            projectId: $project,
            user: $request->user(),
            data: $data
        );

        return $this->returnData(
            __('messages.projects.updated'),
            200,
            new ProjectResource($updatedProject)
        );
    }

    public function destroy(
        Request $request,
        int $project
    ): JsonResponse {
        abort_unless(
            $request->user()->can('delete-project'),
            403
        );

        $this->projectService->delete(
            projectId: $project,
            user: $request->user()
        );

        return $this->success(
            __('messages.projects.deleted'),
            200
        );
    }

    public function bulkDestroy(
        BulkDeleteProjectsRequest $request
    ): JsonResponse {
        $data = BulkDeleteProjectsData::fromArray(
            userId: $request->user()->id,
            data: $request->validated()
        );

        $deletedCount = $this->projectService->bulkDelete($data);

        return $this->returnData(
            __('messages.projects.bulk_deleted'),
            200,
            [
                'deleted_count' => $deletedCount,
            ]
        );
    }
}