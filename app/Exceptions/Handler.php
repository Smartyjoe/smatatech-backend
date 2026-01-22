<?php

namespace App\Exceptions;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return $this->handleApiException($e);
            }
        });
    }

    /**
     * Handle API exceptions and return standardized JSON responses.
     * 
     * All API error responses follow this format:
     * {
     *   "success": false,
     *   "data": null,
     *   "error": {
     *     "code": "ERROR_CODE",
     *     "message": "Human-readable message",
     *     "details": any | null
     *   }
     * }
     */
    protected function handleApiException(Throwable $e): JsonResponse
    {
        // Validation errors
        if ($e instanceof ValidationException) {
            return ApiResponse::validationError(
                $e->errors(),
                'The given data was invalid.'
            );
        }

        // Authentication errors
        if ($e instanceof AuthenticationException) {
            return ApiResponse::unauthorized('Authentication required.');
        }

        // Model not found (e.g., findOrFail)
        if ($e instanceof ModelNotFoundException) {
            $model = class_basename($e->getModel());
            return ApiResponse::notFound("{$model} not found.");
        }

        // Route not found
        if ($e instanceof NotFoundHttpException) {
            return ApiResponse::error(
                ErrorCode::NOT_FOUND,
                'The requested endpoint was not found.',
                null,
                404
            );
        }

        // Method not allowed
        if ($e instanceof MethodNotAllowedHttpException) {
            return ApiResponse::error(
                ErrorCode::METHOD_NOT_ALLOWED,
                'The HTTP method is not allowed for this endpoint.',
                ['allowed' => $e->getHeaders()['Allow'] ?? null],
                405
            );
        }

        // Rate limiting
        if ($e instanceof TooManyRequestsHttpException) {
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
            return ApiResponse::rateLimitExceeded((int) $retryAfter);
        }

        // Other HTTP exceptions
        if ($e instanceof HttpException) {
            $code = $this->mapHttpStatusToErrorCode($e->getStatusCode());
            return ApiResponse::error(
                $code,
                $e->getMessage() ?: 'An HTTP error occurred.',
                null,
                $e->getStatusCode()
            );
        }

        // For other exceptions in production, return a generic error
        if (app()->environment('production')) {
            return ApiResponse::serverError('An unexpected error occurred. Please try again later.');
        }

        // In development, show the actual error for debugging
        return ApiResponse::error(
            ErrorCode::SERVER_ERROR,
            $e->getMessage(),
            [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => collect($e->getTrace())->take(5)->map(fn($t) => [
                    'file' => $t['file'] ?? null,
                    'line' => $t['line'] ?? null,
                    'function' => $t['function'] ?? null,
                ])->toArray(),
            ],
            500
        );
    }

    /**
     * Map HTTP status code to error code.
     */
    protected function mapHttpStatusToErrorCode(int $status): string
    {
        return match ($status) {
            400 => ErrorCode::BAD_REQUEST,
            401 => ErrorCode::AUTH_REQUIRED,
            403 => ErrorCode::FORBIDDEN,
            404 => ErrorCode::NOT_FOUND,
            405 => ErrorCode::METHOD_NOT_ALLOWED,
            409 => ErrorCode::CONFLICT,
            422 => ErrorCode::VALIDATION_ERROR,
            429 => ErrorCode::RATE_LIMIT_EXCEEDED,
            500 => ErrorCode::SERVER_ERROR,
            503 => ErrorCode::SERVICE_UNAVAILABLE,
            default => ErrorCode::SERVER_ERROR,
        };
    }
}
