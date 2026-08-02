<?php

namespace App\Modules\Dashboard\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class DashboardStatisticsException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.dashboard.failed'),
            500
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage(),
        ], 500);
    }
}