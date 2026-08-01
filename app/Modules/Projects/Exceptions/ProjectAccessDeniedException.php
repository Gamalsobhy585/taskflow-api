<?php

namespace App\Modules\Projects\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class ProjectAccessDeniedException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.projects.access_denied'),
            403
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $this->getMessage(),
        ], 403);
    }
}