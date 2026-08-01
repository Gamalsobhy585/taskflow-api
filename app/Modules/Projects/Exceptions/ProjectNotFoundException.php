<?php

namespace App\Modules\Projects\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ProjectNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.projects.not_found'),
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