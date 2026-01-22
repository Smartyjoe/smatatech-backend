<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validate API Request Middleware
 * 
 * Automatically validates incoming requests against their API contract schemas.
 * This ensures requests conform to the documented API specification.
 * 
 * This middleware is designed to fail gracefully - if contracts cannot be loaded,
 * the request will proceed without validation.
 */
class ValidateApiRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if validation is enabled
        if (!config('api.request_validation.enabled', false)) {
            return $next($request);
        }

        try {
            // Lazy load the contract classes only when needed
            $contract = $this->findContract($request);
            
            if (!$contract) {
                // No contract found - allow request to proceed
                return $next($request);
            }

            // Store contract on request for later use
            $request->attributes->set('api_contract', $contract);

            // Validate request body for POST, PUT, PATCH
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH']) && $contract->requestBody) {
                $validation = $this->validateRequestBody($request, $contract);
                if ($validation !== true) {
                    return $validation;
                }
            }

            // Validate query parameters
            if ($contract->queryParams) {
                $validation = $this->validateQueryParams($request, $contract);
                if ($validation !== true) {
                    return $validation;
                }
            }

            // Validate path parameters
            if ($contract->pathParams) {
                $validation = $this->validatePathParams($request, $contract);
                if ($validation !== true) {
                    return $validation;
                }
            }

            // Check for strict mode - reject unknown fields
            if (config('api.request_validation.strict_mode', false)) {
                $validation = $this->validateNoExtraFields($request, $contract);
                if ($validation !== true) {
                    return $validation;
                }
            }
        } catch (\Throwable $e) {
            // Log the error but don't break the API
            Log::warning('API Request Validation middleware error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            
            // Continue without validation
        }

        return $next($request);
    }

    /**
     * Find the API contract for the current request.
     * 
     * @return \App\Api\Contracts\EndpointContract|null
     */
    protected function findContract(Request $request)
    {
        // Lazy load ApiRegistry to avoid autoload issues
        if (!class_exists(\App\Api\Contracts\ApiRegistry::class)) {
            return null;
        }

        $method = $request->method();
        $path = '/' . ltrim($request->path(), '/');
        
        // Remove version prefix if present (e.g., /api/v1/users -> /api/users)
        $path = preg_replace('#^/api/v\d+#', '/api', $path);

        // Try exact match first
        $contract = \App\Api\Contracts\ApiRegistry::get($method, $path);
        
        if ($contract) {
            return $contract;
        }

        // Try pattern matching for path parameters
        $allContracts = \App\Api\Contracts\ApiRegistry::all();
        
        foreach ($allContracts as $key => $contract) {
            if (strtoupper($contract->method) !== strtoupper($method)) {
                continue;
            }

            // Convert contract path to regex pattern
            // e.g., /api/users/{id} -> /api/users/[^/]+
            $pattern = preg_replace('/\{[^}]+\}/', '[^/]+', $contract->path);
            $pattern = '#^' . $pattern . '$#';

            if (preg_match($pattern, $path)) {
                return $contract;
            }
        }

        return null;
    }

    /**
     * Validate request body against contract schema.
     * 
     * @param \App\Api\Contracts\EndpointContract $contract
     */
    protected function validateRequestBody(Request $request, $contract): bool|Response
    {
        $rules = $contract->requestBody->toValidationRules();
        
        if (empty($rules)) {
            return true;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $this->logValidationFailure('request_body', $request, $validator->errors()->toArray());
            
            return \App\Api\ApiResponse::validationError(
                $validator->errors()->toArray(),
                'Request validation failed'
            );
        }

        return true;
    }

    /**
     * Validate query parameters against contract schema.
     * 
     * @param \App\Api\Contracts\EndpointContract $contract
     */
    protected function validateQueryParams(Request $request, $contract): bool|Response
    {
        $rules = $contract->queryParams->toValidationRules();
        
        if (empty($rules)) {
            return true;
        }

        $validator = Validator::make($request->query(), $rules);

        if ($validator->fails()) {
            $this->logValidationFailure('query_params', $request, $validator->errors()->toArray());
            
            return \App\Api\ApiResponse::validationError(
                $validator->errors()->toArray(),
                'Query parameter validation failed'
            );
        }

        return true;
    }

    /**
     * Validate path parameters against contract schema.
     * 
     * @param \App\Api\Contracts\EndpointContract $contract
     */
    protected function validatePathParams(Request $request, $contract): bool|Response
    {
        $rules = $contract->pathParams->toValidationRules();
        
        if (empty($rules)) {
            return true;
        }

        // Get path parameters from route
        $pathParams = $request->route()?->parameters() ?? [];

        $validator = Validator::make($pathParams, $rules);

        if ($validator->fails()) {
            $this->logValidationFailure('path_params', $request, $validator->errors()->toArray());
            
            return \App\Api\ApiResponse::validationError(
                $validator->errors()->toArray(),
                'Path parameter validation failed'
            );
        }

        return true;
    }

    /**
     * Validate that no extra fields are present (strict mode).
     * 
     * @param \App\Api\Contracts\EndpointContract $contract
     */
    protected function validateNoExtraFields(Request $request, $contract): bool|Response
    {
        $allowedFields = [];

        if ($contract->requestBody) {
            $schema = $contract->requestBody->toArray();
            $allowedFields = array_keys($schema['properties'] ?? []);
        }

        $inputFields = array_keys($request->all());
        $extraFields = array_diff($inputFields, $allowedFields);

        if (!empty($extraFields)) {
            $this->logValidationFailure('strict_mode', $request, ['extra_fields' => $extraFields]);
            
            return \App\Api\ApiResponse::error(
                \App\Api\ErrorCode::VALIDATION_ERROR,
                'Unknown fields in request',
                ['unknown_fields' => array_values($extraFields)],
                422
            );
        }

        return true;
    }

    /**
     * Log validation failures.
     */
    protected function logValidationFailure(string $type, Request $request, array $errors): void
    {
        if (!config('api.request_validation.log_failures', true)) {
            return;
        }

        Log::warning('API Request Validation Failed', [
            'type' => $type,
            'method' => $request->method(),
            'path' => $request->path(),
            'errors' => $errors,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }
}
