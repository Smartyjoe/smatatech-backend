<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

/**
 * Base API Controller
 * 
 * All API controllers should extend this class.
 * Provides standardized response methods following the API contract.
 * 
 * Response Format:
 * {
 *   "success": boolean,
 *   "data": any | null,
 *   "error": { "code": string, "message": string, "details": any } | null,
 *   "meta": object | null (for paginated responses),
 *   "message": string | null
 * }
 */
abstract class BaseApiController extends Controller
{
    /**
     * Return a success response.
     * 
     * @param mixed $data Response data
     * @param string|null $message Optional success message
     * @param int $code HTTP status code
     */
    protected function successResponse(mixed $data = null, ?string $message = null, int $code = 200): JsonResponse
    {
        return ApiResponse::success($data, $message, $code);
    }

    /**
     * Return a paginated success response.
     * 
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator
     * @param string|null $message Optional success message
     */
    protected function paginatedResponse($paginator, ?string $message = null): JsonResponse
    {
        $meta = [
            'currentPage' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];

        return ApiResponse::paginated($paginator->items(), $meta, $message);
    }

    /**
     * Return an error response with error code.
     * 
     * @param string $code Error code from ErrorCode constants
     * @param string $message Human-readable error message
     * @param mixed $details Additional error details
     * @param int $httpStatus HTTP status code
     */
    protected function errorWithCode(string $code, string $message, mixed $details = null, ?int $httpStatus = null): JsonResponse
    {
        $status = $httpStatus ?? ErrorCode::getHttpStatus($code);
        return ApiResponse::error($code, $message, $details, $status);
    }

    /**
     * Return an error response (backward compatible).
     * 
     * @param string $message Error message
     * @param array $errors Validation errors or details
     * @param int $code HTTP status code
     */
    protected function errorResponse(string $message, array $errors = [], int $code = 400): JsonResponse
    {
        $errorCode = match ($code) {
            401 => ErrorCode::AUTH_REQUIRED,
            403 => ErrorCode::FORBIDDEN,
            404 => ErrorCode::NOT_FOUND,
            422 => ErrorCode::VALIDATION_ERROR,
            429 => ErrorCode::RATE_LIMIT_EXCEEDED,
            500 => ErrorCode::SERVER_ERROR,
            default => ErrorCode::BAD_REQUEST,
        };

        return ApiResponse::error(
            $errorCode,
            $message,
            !empty($errors) ? $errors : null,
            $code
        );
    }

    /**
     * Return a created response (201).
     */
    protected function createdResponse(mixed $data = null, string $message = 'Resource created successfully'): JsonResponse
    {
        return ApiResponse::created($data, $message);
    }

    /**
     * Return a no content response (204).
     */
    protected function noContentResponse(): JsonResponse
    {
        return response()->json(null, 204);
    }

    /**
     * Return an unauthorized response (401).
     */
    protected function unauthorizedResponse(string $message = 'Authentication required'): JsonResponse
    {
        return ApiResponse::unauthorized($message);
    }

    /**
     * Return a forbidden response (403).
     */
    protected function forbiddenResponse(string $message = 'You do not have permission to access this resource'): JsonResponse
    {
        return ApiResponse::forbidden($message);
    }

    /**
     * Return a not found response (404).
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return ApiResponse::notFound($message);
    }

    /**
     * Return a validation error response (422).
     */
    protected function validationErrorResponse(array $errors, string $message = 'The given data was invalid.'): JsonResponse
    {
        return ApiResponse::validationError($errors, $message);
    }

    /**
     * Return a rate limit exceeded response (429).
     */
    protected function rateLimitResponse(int $retryAfter = 60): JsonResponse
    {
        return ApiResponse::rateLimitExceeded($retryAfter);
    }

    /**
     * Return a server error response (500).
     */
    protected function serverErrorResponse(string $message = 'An unexpected error occurred'): JsonResponse
    {
        return ApiResponse::serverError($message);
    }

    /**
     * Return an insufficient credits response (402).
     */
    protected function insufficientCreditsResponse(string $message = 'Insufficient credits'): JsonResponse
    {
        return ApiResponse::error(
            ErrorCode::INSUFFICIENT_CREDITS,
            $message,
            null,
            402
        );
    }

    /**
     * Return a feature disabled response (403).
     */
    protected function featureDisabledResponse(string $feature): JsonResponse
    {
        return ApiResponse::error(
            ErrorCode::FEATURE_DISABLED,
            "The {$feature} feature is currently disabled",
            null,
            403
        );
    }
}
