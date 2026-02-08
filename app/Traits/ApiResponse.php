<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param  mixed  $data
     * @param  string  $message
     * @param  int  $code
     * @return JsonResponse
     */
    protected function successResponse(mixed $data, string $message = 'success', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send a error response.
     *
     * @param  string  $message
     * @param  int  $code
     * @param  mixed  $data
     * @return JsonResponse
     */
    protected function errorResponse(string $message, int $code = 400, mixed $data = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send a validation error response.
     *
     * @param  mixed  $errors
     * @param  string  $message
     * @param  int  $code
     * @return JsonResponse
     */
    protected function validationErrorResponse(mixed $errors, string $message = 'Validation Error', int $code = 422): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
