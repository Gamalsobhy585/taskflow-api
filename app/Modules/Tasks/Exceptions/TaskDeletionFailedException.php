<?php

namespace App\Modules\Tasks\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TaskDeletionFailedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.tasks.delete_failed'),
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