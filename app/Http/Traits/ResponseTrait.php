<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Response;

trait ResponseTrait
{
    protected function returnDataWithPagination(
        string $message,
        int $statusCode,
        mixed $data
    ): JsonResponse {
        return Response::json([
            'status' => 'success',
            'code' => $statusCode,
            'message' => $message,
            'data' => $data->resolve(request()),
            'pagination' => $data->additional['pagination'] ?? null,
        ], $statusCode);
    }

    public function returnError(
        string $message,
        int $statusCode
    ): never {
        abort($statusCode, $message);
    }

    public function success(
        string $message,
        int $statusCode = 200
    ): JsonResponse {
        return Response::json([
            'status' => 'success',
            'code' => $statusCode,
            'message' => $message,
        ], $statusCode);
    }

    public function returnErrorNotAbort(
        string $message,
        int $statusCode
    ): JsonResponse {
        return Response::json([
            'status' => 'error',
            'code' => $statusCode,
            'message' => $message,
        ], $statusCode);
    }

    public function returnData(
        string $message,
        int $statusCode,
        mixed $value
    ): JsonResponse {
        if ($value instanceof JsonResource) {
            $value = $value->resolve(request());
        }

        return Response::json([
            'status' => 'success',
            'code' => $statusCode,
            'message' => $message,
            'data' => $value,
        ], $statusCode);
    }
}