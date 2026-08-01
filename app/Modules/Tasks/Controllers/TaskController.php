<?php

namespace App\Modules\Tasks\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Modules\Tasks\DTOs\BulkDeleteTasksData;
use App\Modules\Tasks\DTOs\CreateTaskData;
use App\Modules\Tasks\DTOs\TaskFilterData;
use App\Modules\Tasks\DTOs\UpdateTaskData;
use App\Modules\Tasks\Requests\BulkDeleteTasksRequest;
use App\Modules\Tasks\Requests\ListTasksRequest;
use App\Modules\Tasks\Requests\StoreTaskRequest;
use App\Modules\Tasks\Requests\UpdateTaskRequest;
use App\Modules\Tasks\Resources\TaskCollection;
use App\Modules\Tasks\Resources\TaskResource;
use App\Modules\Tasks\Services\Interface\ITaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private readonly ITaskService $taskService
    ) {
    }

    public function index(
        ListTasksRequest $request
    ): TaskCollection {
        $filters = TaskFilterData::fromArray(
            userId: $request->user()->id,
            data: $request->validated()
        );

        return new TaskCollection(
            $this->taskService->list($filters)
        );
    }

    public function store(
        StoreTaskRequest $request
    ): JsonResponse {
        $validated = $request->validated();

        $data = CreateTaskData::fromArray(
            projectId: (int) $validated['project_id'],
            data: $validated
        );

        $task = $this->taskService->create(
            user: $request->user(),
            data: $data
        );

        return $this->returnData(
            __('messages.tasks.created'),
            201,
            new TaskResource($task->load('project'))
        );
    }

    public function show(
        Request $request,
        int $task
    ): JsonResponse {
        abort_unless(
            $request->user()->can('view-task'),
            403
        );

        $taskModel = $this->taskService->find(
            taskId: $task,
            user: $request->user()
        );

        return $this->returnData(
            __('messages.tasks.retrieved'),
            200,
            new TaskResource($taskModel)
        );
    }

    public function update(
        UpdateTaskRequest $request,
        int $task
    ): JsonResponse {
        $data = UpdateTaskData::fromArray(
            $request->validated()
        );

        $updatedTask = $this->taskService->update(
            taskId: $task,
            user: $request->user(),
            data: $data
        );

        return $this->returnData(
            __('messages.tasks.updated'),
            200,
            new TaskResource(
                $updatedTask->load('project')
            )
        );
    }

    public function destroy(
        Request $request,
        int $task
    ): JsonResponse {
        abort_unless(
            $request->user()->can('delete-task'),
            403
        );

        $this->taskService->delete(
            taskId: $task,
            user: $request->user()
        );

        return $this->success(
            __('messages.tasks.deleted'),
            200
        );
    }

    public function bulkDestroy(
        BulkDeleteTasksRequest $request
    ): JsonResponse {
        $data = BulkDeleteTasksData::fromArray(
            userId: $request->user()->id,
            data: $request->validated()
        );

        $deletedCount = $this->taskService->bulkDelete($data);

        return $this->returnData(
            __('messages.tasks.bulk_deleted'),
            200,
            [
                'deleted_count' => $deletedCount,
            ]
        );
    }
}