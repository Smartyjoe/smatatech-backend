<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validate API Response Middleware
 * 
 * Validates outgoing responses against their API contract schemas.
 * Only active in development/testing environments to catch contract violations.
 * 
 * This middleware is designed to fail gracefully - if contracts cannot be loaded,
 * the response will be returned without validation.
 */
class ValidateApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if response validation is enabled
        if (!$this->shouldValidate()) {
            return $response;
        }

        try {
            // Only validate JSON responses
            if (!$response instanceof JsonResponse) {
                return $response;
            }

            // Get the contract from the request (set by ValidateApiRequest middleware)
            $contract = $request->attributes->get('api_contract');
            
            if (!$contract || !$contract->response) {
                return $response;
            }

            // Validate the response
            $violations = $this->validateResponse($response, $contract);

            if (!empty($violations)) {
                $this->handleViolations($request, $response, $contract, $violations);
            }
        } catch (\Throwable $e) {
            // Log the error but don't break the API
            Log::warning('API Response Validation middleware error: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }

        return $response;
    }

    /**
     * Check if response validation should run.
     */
    protected function shouldValidate(): bool
    {
        if (!config('api.response_validation.enabled', false)) {
            return false;
        }

        $allowedEnvs = config('api.response_validation.environments', ['local', 'testing', 'staging']);
        
        return in_array(app()->environment(), $allowedEnvs);
    }

    /**
     * Validate response against contract schema.
     * 
     * @param \App\Api\Contracts\EndpointContract $contract
     */
    protected function validateResponse(JsonResponse $response, $contract): array
    {
        $violations = [];
        $data = $response->getData(true);

        // Check standard response structure
        $violations = array_merge($violations, $this->validateResponseStructure($data));

        // If it's an error response, skip data schema validation
        if (isset($data['success']) && $data['success'] === false) {
            $violations = array_merge($violations, $this->validateErrorStructure($data));
            return $violations;
        }

        // Validate the data field against the contract response schema
        if (isset($data['data']) && $contract->response) {
            $schemaViolations = $this->validateAgainstSchema(
                $data['data'],
                $contract->response->toArray()
            );
            $violations = array_merge($violations, $schemaViolations);
        }

        return $violations;
    }

    /**
     * Validate standard API response structure.
     */
    protected function validateResponseStructure(array $data): array
    {
        $violations = [];

        // Check required fields
        if (!array_key_exists('success', $data)) {
            $violations[] = [
                'field' => 'success',
                'message' => 'Response missing required "success" field',
            ];
        }

        if (!array_key_exists('data', $data)) {
            $violations[] = [
                'field' => 'data',
                'message' => 'Response missing required "data" field',
            ];
        }

        if (!array_key_exists('error', $data)) {
            $violations[] = [
                'field' => 'error',
                'message' => 'Response missing required "error" field',
            ];
        }

        // Validate success field type
        if (isset($data['success']) && !is_bool($data['success'])) {
            $violations[] = [
                'field' => 'success',
                'message' => 'Field "success" must be a boolean',
            ];
        }

        return $violations;
    }

    /**
     * Validate error response structure.
     */
    protected function validateErrorStructure(array $data): array
    {
        $violations = [];

        if (!isset($data['error']) || !is_array($data['error'])) {
            $violations[] = [
                'field' => 'error',
                'message' => 'Error response must have an "error" object',
            ];
            return $violations;
        }

        $error = $data['error'];

        if (!isset($error['code'])) {
            $violations[] = [
                'field' => 'error.code',
                'message' => 'Error object missing required "code" field',
            ];
        }

        if (!isset($error['message'])) {
            $violations[] = [
                'field' => 'error.message',
                'message' => 'Error object missing required "message" field',
            ];
        }

        return $violations;
    }

    /**
     * Validate data against a schema definition.
     */
    protected function validateAgainstSchema(mixed $data, array $schema, string $path = 'data'): array
    {
        $violations = [];

        // Handle null data
        if ($data === null) {
            return $violations;
        }

        $type = $schema['type'] ?? 'object';

        // Validate type
        if (!$this->matchesType($data, $type)) {
            $violations[] = [
                'field' => $path,
                'message' => "Expected type '{$type}', got " . gettype($data),
            ];
            return $violations;
        }

        // Validate object properties
        if ($type === 'object' && isset($schema['properties'])) {
            $violations = array_merge(
                $violations,
                $this->validateObjectProperties($data, $schema['properties'], $schema['required'] ?? [], $path)
            );
        }

        // Validate array items
        if ($type === 'array' && isset($schema['items']) && is_array($data)) {
            foreach ($data as $index => $item) {
                $violations = array_merge(
                    $violations,
                    $this->validateAgainstSchema($item, $schema['items'], "{$path}[{$index}]")
                );
            }
        }

        return $violations;
    }

    /**
     * Validate object properties against schema.
     */
    protected function validateObjectProperties(array|object $data, array $properties, array $required, string $path): array
    {
        $violations = [];
        $data = (array) $data;

        // Check required fields
        foreach ($required as $field) {
            if (!array_key_exists($field, $data)) {
                $violations[] = [
                    'field' => "{$path}.{$field}",
                    'message' => "Required field '{$field}' is missing",
                ];
            }
        }

        // Validate each property
        foreach ($properties as $name => $propSchema) {
            if (!array_key_exists($name, $data)) {
                continue;
            }

            $value = $data[$name];
            $propType = is_array($propSchema['type'] ?? null) 
                ? ($propSchema['type']['type'] ?? 'string')
                : ($propSchema['type'] ?? 'string');

            // Skip null values for optional fields
            if ($value === null && !in_array($name, $required)) {
                continue;
            }

            if (!$this->matchesType($value, $propType)) {
                $violations[] = [
                    'field' => "{$path}.{$name}",
                    'message' => "Expected type '{$propType}', got " . gettype($value),
                ];
            }

            // Recursively validate nested objects
            if (is_array($propSchema['type'] ?? null) && isset($propSchema['type']['properties'])) {
                $violations = array_merge(
                    $violations,
                    $this->validateAgainstSchema($value, $propSchema['type'], "{$path}.{$name}")
                );
            }
        }

        return $violations;
    }

    /**
     * Check if a value matches the expected type.
     */
    protected function matchesType(mixed $value, string $type): bool
    {
        return match ($type) {
            'string' => is_string($value),
            'integer', 'int' => is_int($value),
            'number', 'float', 'double' => is_numeric($value),
            'boolean', 'bool' => is_bool($value),
            'array' => is_array($value) && array_is_list($value),
            'object' => is_array($value) || is_object($value),
            'null' => is_null($value),
            'uuid' => is_string($value) && preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value),
            'email' => is_string($value) && filter_var($value, FILTER_VALIDATE_EMAIL),
            'url' => is_string($value) && filter_var($value, FILTER_VALIDATE_URL),
            'datetime', 'date' => is_string($value),
            'any', 'mixed' => true,
            default => true,
        };
    }

    /**
     * Handle contract violations.
     * 
     * @param \App\Api\Contracts\EndpointContract $contract
     */
    protected function handleViolations(Request $request, JsonResponse $response, $contract, array $violations): void
    {
        // Log violations
        if (config('api.response_validation.log_violations', true)) {
            Log::warning('API Response Contract Violation', [
                'endpoint' => $contract->method . ' ' . $contract->path,
                'endpoint_name' => $contract->name,
                'status_code' => $response->getStatusCode(),
                'violations' => $violations,
                'request_path' => $request->path(),
            ]);
        }

        // Optionally throw exception in strict mode
        if (config('api.response_validation.throw_exceptions', false)) {
            throw new \RuntimeException(
                'API Response Contract Violation: ' . json_encode($violations)
            );
        }

        // Add violation info to response headers in development
        if (app()->environment('local', 'testing')) {
            $response->headers->set('X-API-Contract-Violations', json_encode($violations));
        }
    }
}
