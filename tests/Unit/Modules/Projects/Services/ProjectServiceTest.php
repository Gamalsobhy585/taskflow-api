<?php

namespace Tests\Unit\Modules\Projects\Services;

use App\Enums\ProjectStatus;
use App\Models\Project;
use App\Models\User;
use App\Modules\Projects\Cache\Interface\IProjectCache;
use App\Modules\Projects\DTOs\CreateProjectData;
use App\Modules\Projects\Exceptions\ProjectNotFoundException;
use App\Modules\Projects\Repositories\Interface\IProjectRepository;
use App\Modules\Projects\Services\Implementation\ProjectService;
use Closure;
use Mockery;
use Tests\TestCase;

class ProjectServiceTest extends TestCase
{
    private IProjectRepository $projectRepository;

    private IProjectCache $projectCache;

    private ProjectService $projectService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectRepository = Mockery::mock(
            IProjectRepository::class
        );

        $this->projectCache = Mockery::mock(
            IProjectCache::class
        );

        $this->projectService = new ProjectService(
            projectRepository: $this->projectRepository,
            projectCache: $this->projectCache
        );
    }

    public function test_it_creates_project(): void
    {
        $data = new CreateProjectData(
            userId: 1,
            name: 'Assessment Project',
            description: 'Description',
            status: ProjectStatus::ACTIVE
        );

        $project = new Project($data->toArray());
        $project->id = 10;

        $this->projectRepository
            ->shouldReceive('create')
            ->once()
            ->with($data)
            ->andReturn($project);

        $this->projectCache
            ->shouldReceive('flushForUser')
            ->once()
            ->with(1);

        $result = $this->projectService->create($data);

        $this->assertSame(10, $result->id);

        $this->assertSame(
            'Assessment Project',
            $result->name
        );
    }

    public function test_it_throws_exception_when_project_is_not_owned_by_user(): void
    {
        $user = new User();
        $user->id = 2;

        $this->projectCache
            ->shouldReceive('rememberProject')
            ->once()
            ->with(
                2,
                50,
                Mockery::type(Closure::class)
            )
            ->andReturnUsing(
                function (
                    int $userId,
                    int $projectId,
                    Closure $callback
                ): ?Project {
                    return $callback();
                }
            );

        $this->projectRepository
            ->shouldReceive('findForUser')
            ->once()
            ->with(50, 2)
            ->andReturnNull();

        $this->expectException(
            ProjectNotFoundException::class
        );

        $this->projectService->find(50, $user);
    }

    public function test_it_returns_project_from_redis_cache(): void
    {
        $user = new User();
        $user->id = 1;

        $cachedProject = new Project([
            'user_id' => 1,
            'name' => 'Cached project',
            'description' => 'Cached description',
            'status' => ProjectStatus::ACTIVE,
        ]);

        $cachedProject->id = 20;

        $this->projectCache
            ->shouldReceive('rememberProject')
            ->once()
            ->with(
                1,
                20,
                Mockery::type(Closure::class)
            )
            ->andReturn($cachedProject);

        /*
         * Because Redis returned the project,
         * the database repository must not be called.
         */
        $this->projectRepository
            ->shouldNotReceive('findForUser');

        $result = $this->projectService->find(
            projectId: 20,
            user: $user
        );

        $this->assertSame(20, $result->id);
        $this->assertSame('Cached project', $result->name);
    }
}