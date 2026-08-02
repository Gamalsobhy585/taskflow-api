<?php

namespace App\Modules\Dashboard\Repositories\Interface;

use App\Modules\Dashboard\DTOs\DashboardStatisticsData;

interface IDashboardRepository
{
    public function getStatisticsForUser(
        int $userId
    ): DashboardStatisticsData;
}