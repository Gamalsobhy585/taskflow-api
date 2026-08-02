<?php

namespace Tests\Unit\Modules\Tasks\Services;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Modules\Tasks\Cache\Interface\ITaskCache;
use App\Modules\Tasks\DTOs\BulkDeleteTasksData;
use App\Modules\Tasks\DTOs\CreateTaskData;
use App\Modules\Tasks\DTOs\TaskFilterData;
use App\Modules\Tasks\DTOs\UpdateTaskData;
use App\Modules\Tasks\Exceptions\ProjectNotFoundException;
use App\Modules\Tasks\Exceptions\TaskDeletionFailedException;
use App\Modules\Tasks\Exceptions\TaskNotFoundException;
use App\Modules\Tasks\Repositories\Interface\ITaskRepository;
use App\Modules\Tasks\Services\Implementation\TaskService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    private ITaskRepository $taskRepository;

    private ITaskCache $taskCache;

    private TaskService $taskService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->taskRepository = Mockery::mock(
            ITaskRepository::class
        );

        $this->taskCache = Mockery::mock(
            ITaskCache::class
        );

        $this->taskService = new TaskService(
            $this->taskRepository,
            $this->taskCache
        );
    }

    public function test_it_creates_task_for_owned_project(): void
    {
        $user = new User();
        $user->id = 1;

        $data = new CreateTaskData(
            projectId: 10,
            title: 'Assessment task',
            description: null,
            priority: TaskPriority::HIGH,
            status: TaskStatus::TODO,
            dueDate: null
        );

        $project = new Project([
            'user_id' => 1,
        ]);

        $project->id = 10;

        $task = new Task($data->toArray());
        $task->id = 20;

        $this->taskRepository
            ->shouldReceive('findOwnedProject')
            ->once()
            ->with(10, 1)
            ->andReturn($project);

        $this->taskRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($task);

        $this->taskCache
            ->shouldReceive('forgetUserTasks')
            ->once()
            ->with(1);

        $result = $this->taskService->create(
            $user,
            $data
        );

        $this->assertSame(20, $result->id);
        $this->assertSame(
            'Assessment task',
            $result->title
        );
    }

    public function test_it_rejects_project_not_owned_by_user(): void
    {
        $user = new User();
        $user->id = 2;

        $data = new CreateTaskData(
            projectId: 10,
            title: 'Unauthorized task',
            description: null,
            priority: TaskPriority::LOW,
            status: TaskStatus::TODO,
            dueDate: null
        );

        $this->taskRepository
            ->shouldReceive('findOwnedProject')
            ->once()
            ->with(10, 2)
            ->andReturnNull();

        $this->taskRepository
            ->shouldNotReceive('create');

        $this->taskCache
            ->shouldNotReceive('forgetUserTasks');

        $this->expectException(
            ProjectNotFoundException::class
        );

        $this->taskService->create($user, $data);
    }

    public function test_it_returns_cached_task_list(): void
    {
        $filters = new TaskFilterData(
            userId: 1,
            projectId: null,
            status: null,
            priority: null,
            search: null,
            perPage: 15,
            page: 1
        );

        $paginator = Mockery::mock(
            LengthAwarePaginator::class
        );

        $this->taskCache
            ->shouldReceive('rememberTaskList')
            ->once()
            ->with(
                $filters,
                Mockery::type('callable')
            )
            ->andReturn($paginator);

        $this->taskRepository
            ->shouldNotReceive('paginateForUser');

        $result = $this->taskService->list($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_it_returns_cached_owned_task(): void
    {
        $user = new User();
        $user->id = 1;

        $task = new Task([
            'project_id' => 10,
            'title' => 'Cached task',
            'priority' => TaskPriority::HIGH,
            'status' => TaskStatus::TODO,
        ]);

        $task->id = 20;

        $this->taskCache
            ->shouldReceive('rememberTask')
            ->once()
            ->with(
                1,
                20,
                Mockery::type('callable')
            )
            ->andReturn($task);

        $result = $this->taskService->find(
            taskId: 20,
            user: $user
        );

        $this->assertSame($task, $result);
    }

    public function test_it_throws_exception_when_task_is_not_owned(): void
    {
        $user = new User();
        $user->id = 2;

        $this->taskCache
            ->shouldReceive('rememberTask')
            ->once()
            ->andReturnUsing(
                fn (
                    int $userId,
                    int $taskId,
                    callable $callback
                ) => $callback()
            );

        $this->taskRepository
            ->shouldReceive('findForUser')
            ->once()
            ->with(20, 2)
            ->andReturnNull();

        $this->expectException(
            TaskNotFoundException::class
        );

        $this->taskService->find(20, $user);
    }

    public function test_it_updates_owned_task_and_clears_cache(): void
    {
        $user = new User();
        $user->id = 1;

        $task = new Task([
            'project_id' => 10,
            'title' => 'Old task',
            'priority' => TaskPriority::LOW,
            'status' => TaskStatus::TODO,
        ]);

        $task->id = 20;

        $data = new UpdateTaskData(
            title: 'Updated task',
            description: 'Updated description',
            priority: TaskPriority::HIGH,
            status: TaskStatus::IN_PROGRESS,
            dueDate: null
        );

        $updatedTask = new Task($data->toArray());
        $updatedTask->id = 20;

        $this->taskCache
            ->shouldReceive('rememberTask')
            ->once()
            ->andReturn($task);

        $this->taskRepository
            ->shouldReceive('update')
            ->once()
            ->with($task, $data)
            ->andReturn($updatedTask);

        $this->taskCache
            ->shouldReceive('forgetUserTasks')
            ->once()
            ->with(1);

        $result = $this->taskService->update(
            taskId: 20,
            user: $user,
            data: $data
        );

        $this->assertSame($updatedTask, $result);
    }

    public function test_it_deletes_owned_task_and_clears_cache(): void
    {
        $user = new User();
        $user->id = 1;

        $task = new Task([
            'project_id' => 10,
            'title' => 'Delete task',
            'priority' => TaskPriority::LOW,
            'status' => TaskStatus::TODO,
        ]);

        $task->id = 20;

        $this->taskCache
            ->shouldReceive('rememberTask')
            ->once()
            ->andReturn($task);

        $this->taskRepository
            ->shouldReceive('delete')
            ->once()
            ->with($task)
            ->andReturnTrue();

        $this->taskCache
            ->shouldReceive('forgetUserTasks')
            ->once()
            ->with(1);

        $this->taskService->delete(20, $user);

        $this->assertTrue(true);
    }

    public function test_it_throws_exception_when_deletion_fails(): void
    {
        $user = new User();
        $user->id = 1;

        $task = new Task([
            'project_id' => 10,
            'title' => 'Delete task',
            'priority' => TaskPriority::LOW,
            'status' => TaskStatus::TODO,
        ]);

        $task->id = 20;

        $this->taskCache
            ->shouldReceive('rememberTask')
            ->once()
            ->andReturn($task);

        $this->taskRepository
            ->shouldReceive('delete')
            ->once()
            ->with($task)
            ->andReturnFalse();

        $this->taskCache
            ->shouldNotReceive('forgetUserTasks');

        $this->expectException(
            TaskDeletionFailedException::class
        );

        $this->taskService->delete(20, $user);
    }

    public function test_it_bulk_deletes_owned_tasks(): void
    {
        $data = new BulkDeleteTasksData(
            userId: 1,
            taskIds: [10, 11, 12]
        );

        $this->taskRepository
            ->shouldReceive('bulkDeleteForUser')
            ->once()
            ->with(1, [10, 11, 12])
            ->andReturn(3);

        $this->taskCache
            ->shouldReceive('forgetUserTasks')
            ->once()
            ->with(1);

        $result = $this->taskService
            ->bulkDelete($data);

        $this->assertSame(3, $result);
    }
}