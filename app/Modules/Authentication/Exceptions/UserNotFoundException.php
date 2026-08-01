<?php

namespace App\Modules\Authentication\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class UserNotFoundException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.renew.user_not_found'),
            404
        );
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $this->getMessage(),
        ], $this->getCode());
    }
}