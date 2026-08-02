<?php

namespace App\Modules\Dashboard\Services\Interface;

use App\Models\User;
use App\Modules\Dashboard\DTOs\DashboardStatisticsData;

interface IDashboardService
{
    public function getStatistics(
        User $user
    ): DashboardStatisticsData;
}