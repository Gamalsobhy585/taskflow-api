<?php

namespace App\Modules\Dashboard\Services\Implementation;

use App\Models\User;
use App\Modules\Dashboard\DTOs\DashboardStatisticsData;
use App\Modules\Dashboard\Exceptions\DashboardStatisticsException;
use App\Modules\Dashboard\Repositories\Interface\IDashboardRepository;
use App\Modules\Dashboard\Services\Interface\IDashboardService;
use Throwable;

class DashboardService implements IDashboardService
{
    public function __construct(
        private readonly IDashboardRepository $dashboardRepository
    ) {
    }

    public function getStatistics(
        User $user
    ): DashboardStatisticsData {
        try {
            return $this->dashboardRepository
                ->getStatisticsForUser($user->id);
        } catch (Throwable $exception) {
            report($exception);

            throw new DashboardStatisticsException();
        }
    }
}