<?php

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        
        // Register middleware aliases
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
            'permission' => \App\Http\Middleware\CheckPermission::class,
            'auth.admin' => \App\Http\Middleware\AdminAuthenticate::class,
            'auth.user' => \App\Http\Middleware\UserAuthenticate::class,
            'api.validate.request' => \App\Http\Middleware\ValidateApiRequest::class,
            'api.validate.response' => \App\Http\Middleware\ValidateApiResponse::class,
        ]);

        // Rate limiting for API (uses the 'api' rate limiter defined in AppServiceProvider)
        $middleware->throttleApi('api');

        // Append API validation middleware to the 'api' middleware group
        // Request validation runs before controller, response validation runs after
        $middleware->appendToGroup('api', [
            \App\Http\Middleware\ValidateApiRequest::class,
            \App\Http\Middleware\ValidateApiResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Handle API exceptions with standardized responses.
         * 
         * All API errors follow this format:
         * {
         *   "success": false,
         *   "data": null,
         *   "error": {
         *     "code": "ERROR_CODE",
         *     "message": "Human readable message",
         *     "details": null | object
         *   }
         * }
         */
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                
                // Validation errors
                if ($e instanceof \Illuminate\Validation\ValidationException) {
                    return ApiResponse::error(
                        ErrorCode::VALIDATION_ERROR,
                        'The given data was invalid.',
                        $e->errors(),
                        422
                    );
                }

                // Authentication errors
                if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                    return ApiResponse::error(
                        ErrorCode::AUTH_REQUIRED,
                        'Authentication required.',
                        null,
                        401
                    );
                }

                // Model not found
                if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                    $model = class_basename($e->getModel());
                    return ApiResponse::error(
                        ErrorCode::RESOURCE_NOT_FOUND,
                        "{$model} not found.",
                        null,
                        404
                    );
                }

                // Route not found
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                    return ApiResponse::error(
                        ErrorCode::NOT_FOUND,
                        'The requested endpoint was not found.',
                        null,
                        404
                    );
                }

                // Method not allowed
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                    return ApiResponse::error(
                        ErrorCode::REQUEST_METHOD_NOT_ALLOWED,
                        'The HTTP method is not allowed for this endpoint.',
                        ['allowedMethods' => $e->getHeaders()['Allow'] ?? null],
                        405
                    );
                }

                // Too many requests
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                    $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;
                    return ApiResponse::error(
                        ErrorCode::RATE_LIMIT_EXCEEDED,
                        "Too many requests. Please wait {$retryAfter} seconds.",
                        ['retryAfter' => (int) $retryAfter],
                        429
                    );
                }

                // Access denied
                if ($e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException) {
                    return ApiResponse::error(
                        ErrorCode::FORBIDDEN,
                        $e->getMessage() ?: 'Access denied.',
                        null,
                        403
                    );
                }

                // Generic HTTP exceptions
                if ($e instanceof HttpException) {
                    $status = $e->getStatusCode();
                    $code = match ($status) {
                        400 => ErrorCode::BAD_REQUEST,
                        401 => ErrorCode::AUTH_REQUIRED,
                        403 => ErrorCode::FORBIDDEN,
                        404 => ErrorCode::NOT_FOUND,
                        405 => ErrorCode::REQUEST_METHOD_NOT_ALLOWED,
                        422 => ErrorCode::VALIDATION_ERROR,
                        429 => ErrorCode::RATE_LIMIT_EXCEEDED,
                        500 => ErrorCode::SERVER_ERROR,
                        503 => ErrorCode::SERVER_UNAVAILABLE,
                        default => ErrorCode::SERVER_ERROR,
                    };
                    
                    return ApiResponse::error(
                        $code,
                        $e->getMessage() ?: 'An error occurred.',
                        null,
                        $status
                    );
                }

                // In production, hide detailed errors
                if (app()->environment('production')) {
                    \Log::error('API Error: ' . $e->getMessage(), [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);

                    return ApiResponse::error(
                        ErrorCode::SERVER_ERROR,
                        'An unexpected error occurred.',
                        null,
                        500
                    );
                }

                // In development, show more details
                return ApiResponse::error(
                    ErrorCode::SERVER_ERROR,
                    $e->getMessage(),
                    [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    500
                );
            }
        });
    })->create();