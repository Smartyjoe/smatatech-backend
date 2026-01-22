<?php

namespace App\Api;

use Illuminate\Http\JsonResponse;

/**
 * Standardized API Response Builder
 * 
 * All API responses MUST use this class to ensure consistent format:
 * {
 *   "success": boolean,
 *   "data": any | null,
 *   "error": { "code": string, "message": string, "details": any | null } | null,
 *   "meta": object | null
 * }
 */
class ApiResponse
{
    /**
     * Create a success response.
     */
    public static function success(mixed $data = null, ?string $message = null, int $status = 200): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'error' => null,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, $status);
    }

    /**
     * Create a success response with pagination meta.
     */
    public static function paginated(mixed $data, array $meta, ?string $message = null): JsonResponse
    {
        $response = [
            'success' => true,
            'data' => $data,
            'error' => null,
            'meta' => $meta,
        ];

        if ($message) {
            $response['message'] = $message;
        }

        return response()->json($response, 200);
    }

    /**
     * Create a created response (201).
     */
    public static function created(mixed $data = null, ?string $message = 'Resource created successfully'): JsonResponse
    {
        return self::success($data, $message, 201);
    }

    /**
     * Create an error response.
     */
    public static function error(
        string $code,
        string $message,
        mixed $details = null,
        int $status = 400
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $status);
    }

    /**
     * Create a validation error response.
     */
    public static function validationError(array $errors, ?string $message = 'Validation failed'): JsonResponse
    {
        return self::error(
            ErrorCode::VALIDATION_ERROR,
            $message,
            $errors,
            422
        );
    }

    /**
     * Create an unauthorized response.
     */
    public static function unauthorized(?string $message = 'Authentication required'): JsonResponse
    {
        return self::error(
            ErrorCode::AUTH_REQUIRED,
            $message,
            null,
            401
        );
    }

    /**
     * Create a forbidden response.
     */
    public static function forbidden(?string $message = 'You do not have permission to access this resource'): JsonResponse
    {
        return self::error(
            ErrorCode::FORBIDDEN,
            $message,
            null,
            403
        );
    }

    /**
     * Create a not found response.
     */
    public static function notFound(?string $message = 'Resource not found'): JsonResponse
    {
        return self::error(
            ErrorCode::NOT_FOUND,
            $message,
            null,
            404
        );
    }

    /**
     * Create a server error response.
     */
    public static function serverError(?string $message = 'An unexpected error occurred'): JsonResponse
    {
        return self::error(
            ErrorCode::SERVER_ERROR,
            $message,
            null,
            500
        );
    }

    /**
     * Create a rate limit exceeded response.
     */
    public static function rateLimitExceeded(int $retryAfter = 60): JsonResponse
    {
        return self::error(
            ErrorCode::RATE_LIMIT_EXCEEDED,
            "Too many requests. Please wait {$retryAfter} seconds.",
            ['retryAfter' => $retryAfter],
            429
        );
    }
}
