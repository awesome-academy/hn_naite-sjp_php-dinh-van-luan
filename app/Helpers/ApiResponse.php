<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use App\Enums\HttpStatusCode;

class ApiResponse
{
    public static function success($data = [], ?string $message = null, int $code = HttpStatusCode::OK): JsonResponse
    {
        return response()->json([
            'status' => true,
            'statusCode' => $code,
            'message' => $message ?? __('success'),
            'data' => $data,
        ], $code);
    }

    public static function error(?string $message = null, $errors = [], int $code = HttpStatusCode::INTERNAL_SERVER_ERROR): JsonResponse
    {
        return response()->json([
            'status' => false,
            'statusCode' => $code,
            'message' => $message ?? __('success'),
            'errors' => $errors,
        ], $code);
    }
}
