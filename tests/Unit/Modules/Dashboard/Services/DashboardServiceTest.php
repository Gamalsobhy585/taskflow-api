<?php

namespace Tests\Unit\Modules\Dashboard\Services;

use App\Models\User;
use App\Modules\Dashboard\DTOs\DashboardStatisticsData;
use App\Modules\Dashboard\Repositories\Interface\IDashboardRepository;
use App\Modules\Dashboard\Services\Implementation\DashboardService;
use Mockery;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    public function test_it_returns_dashboard_statistics(): void
    {
        $repository = Mockery::mock(
            IDashboardRepository::class
        );

        $user = new User();
        $user->id = 1;

        $statistics = new DashboardStatisticsData(
            totalProjects: 5,
            activeProjects: 3,
            totalTasks: 20,
            completedTasks: 8,
            pendingTasks: 12,
            overdueTasks: 4,
        );

        $repository
            ->shouldReceive('getStatisticsForUser')
            ->once()
            ->with(1)
            ->andReturn($statistics);

        $service = new DashboardService($repository);

        $result = $service->getStatistics($user);

        $this->assertSame(5, $result->totalProjects);
        $this->assertSame(3, $result->activeProjects);
        $this->assertSame(20, $result->totalTasks);
        $this->assertSame(8, $result->completedTasks);
        $this->assertSame(12, $result->pendingTasks);
        $this->assertSame(4, $result->overdueTasks);
    }
}