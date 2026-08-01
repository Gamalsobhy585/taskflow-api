<?php

namespace App\Modules\Tasks\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TaskNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.tasks.not_found'),
            404
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage(),
        ], 404);
    }
}