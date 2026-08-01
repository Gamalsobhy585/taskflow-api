<?php

namespace App\Modules\Authentication\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class EmailAlreadyExistsException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            __('messages.register.email_exists'),
            422
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