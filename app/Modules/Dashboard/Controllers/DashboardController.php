<?php

namespace App\Modules\Dashboard\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Modules\Dashboard\Resources\DashboardStatisticsResource;
use App\Modules\Dashboard\Services\Interface\IDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ResponseTrait;

    public function __construct(
        private readonly IDashboardService $dashboardService
    ) {
    }

    public function __invoke(
        Request $request
    ): JsonResponse {
        $statistics = $this->dashboardService
            ->getStatistics($request->user());

        return $this->returnData(
            __('messages.dashboard.success'),
            200,
            new DashboardStatisticsResource($statistics)
        );
    }
}