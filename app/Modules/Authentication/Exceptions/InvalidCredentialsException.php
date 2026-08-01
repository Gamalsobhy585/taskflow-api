<?php

namespace App\Modules\Authentication\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class InvalidCredentialsException extends Exception
{
    public function __construct(?string $message = null)
    {
        parent::__construct(
            $message ?? __('messages.login.invalid_credentials'),
            401
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