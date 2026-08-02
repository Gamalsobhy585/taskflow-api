<?php

namespace App\Modules\Projects\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ProjectDeletionFailedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.projects.delete_failed'),
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