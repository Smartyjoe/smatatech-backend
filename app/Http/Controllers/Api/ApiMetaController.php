<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiResponse;
use App\Api\Contracts\ApiRegistry;
use App\Api\ErrorCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * API Meta Controller
 * 
 * Provides self-documenting endpoints for the API.
 * These endpoints allow frontend developers and services
 * to programmatically discover and understand the API.
 * Supports API versioning.
 */
class ApiMetaController extends BaseApiController
{
    /**
     * GET /api/meta or /api/{version}/meta
     * 
     * Returns the complete API specification.
     * This is the primary endpoint for programmatic API discovery.
     */
    public function spec(Request $request, ?string $version = null): JsonResponse
    {
        $version = $this->resolveVersion($request, $version);
        $cacheKey = "api_spec_{$version}";
        $cacheTtl = config('api.documentation.cache_ttl', 3600);

        $spec = Cache::remember($cacheKey, $cacheTtl, function () use ($version) {
            return ApiRegistry::getSpec($version);
        });

        $response = ApiResponse::success($spec);
        
        // Add version headers
        return $this->addVersionHeaders($response, $version);
    }

    /**
     * GET /api/meta/endpoints or /api/{version}/meta/endpoints
     * 
     * Returns just the endpoints, grouped by resource.
     */
    public function endpoints(Request $request, ?string $version = null): JsonResponse
    {
        $version = $this->resolveVersion($request, $version);
        $cacheKey = "api_endpoints_{$version}";
        $cacheTtl = config('api.documentation.cache_ttl', 3600);

        $endpoints = Cache::remember($cacheKey, $cacheTtl, function () use ($version) {
            return ApiRegistry::grouped($version);
        });

        $response = ApiResponse::success($endpoints);
        
        return $this->addVersionHeaders($response, $version);
    }

    /**
     * GET /api/meta/versions
     * 
     * Returns information about available API versions.
     */
    public function versions(): JsonResponse
    {
        $supported = ApiRegistry::getSupportedVersions();
        $default = config('api.versioning.default', 'v1');
        
        return ApiResponse::success([
            'current' => $default,
            'supported' => $supported,
            'latest' => end($supported) ?: 'v1',
            'deprecated' => [], // Add deprecated versions here
            'header' => config('api.versioning.header', 'X-API-Version'),
            'urlPattern' => '/api/{version}/*',
            'documentation' => [
                'v1' => url('/api/v1/swagger'),
            ],
        ]);
    }

    /**
     * Resolve API version from request.
     */
    protected function resolveVersion(Request $request, ?string $version): string
    {
        // Explicit version parameter takes priority
        if ($version && $this->isValidVersion($version)) {
            return $version;
        }

        // Check URL path
        $path = $request->path();
        if (preg_match('/api\/(v\d+)/', $path, $matches)) {
            if ($this->isValidVersion($matches[1])) {
                return $matches[1];
            }
        }

        // Check header
        $headerVersion = $request->header(config('api.versioning.header', 'X-API-Version'));
        if ($headerVersion) {
            $normalized = str_starts_with($headerVersion, 'v') ? $headerVersion : 'v' . $headerVersion;
            if ($this->isValidVersion($normalized)) {
                return $normalized;
            }
        }

        // Check query parameter
        $queryVersion = $request->query('version');
        if ($queryVersion) {
            $normalized = str_starts_with($queryVersion, 'v') ? $queryVersion : 'v' . $queryVersion;
            if ($this->isValidVersion($normalized)) {
                return $normalized;
            }
        }

        return config('api.versioning.default', 'v1');
    }

    /**
     * Check if version is valid.
     */
    protected function isValidVersion(string $version): bool
    {
        return in_array($version, ApiRegistry::getSupportedVersions());
    }

    /**
     * Add version headers to response.
     */
    protected function addVersionHeaders(JsonResponse $response, string $version): JsonResponse
    {
        $response->headers->set('X-API-Version', $version);
        
        $supported = ApiRegistry::getSupportedVersions();
        $latest = end($supported) ?: 'v1';
        
        if ($version !== $latest && config('api.versioning.deprecation_warning', true)) {
            $response->headers->set('X-API-Version-Warning', "Version {$version} is not the latest. Consider upgrading to {$latest}.");
        }
        
        return $response;
    }

    /**
     * GET /api/meta/errors
     * 
     * Returns all error codes with descriptions and HTTP statuses.
     */
    public function errors(): JsonResponse
    {
        return ApiResponse::success([
            'errorCodes' => ErrorCode::all(),
            'usage' => [
                'description' => 'All error responses follow this format',
                'schema' => [
                    'success' => false,
                    'data' => null,
                    'error' => [
                        'code' => 'ERROR_CODE',
                        'message' => 'Human readable message',
                        'details' => 'Additional details or validation errors',
                    ],
                ],
            ],
        ]);
    }

    /**
     * GET /api/meta/schemas
     * 
     * Returns common schema definitions.
     */
    public function schemas(): JsonResponse
    {
        return ApiResponse::success([
            'response' => [
                'success' => [
                    'description' => 'Standard success response',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'value' => true],
                        'data' => ['type' => 'any', 'description' => 'Response data'],
                        'error' => ['type' => 'null'],
                        'message' => ['type' => 'string', 'optional' => true],
                        'meta' => ['type' => 'object', 'optional' => true, 'description' => 'Pagination metadata'],
                    ],
                ],
                'error' => [
                    'description' => 'Standard error response',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'value' => false],
                        'data' => ['type' => 'null'],
                        'error' => [
                            'type' => 'object',
                            'properties' => [
                                'code' => ['type' => 'string', 'description' => 'Error code from ErrorCode constants'],
                                'message' => ['type' => 'string', 'description' => 'Human-readable error message'],
                                'details' => ['type' => 'any', 'description' => 'Additional error details'],
                            ],
                        ],
                    ],
                ],
                'paginated' => [
                    'description' => 'Paginated response with meta',
                    'properties' => [
                        'success' => ['type' => 'boolean', 'value' => true],
                        'data' => ['type' => 'array', 'description' => 'Array of items'],
                        'error' => ['type' => 'null'],
                        'meta' => [
                            'type' => 'object',
                            'properties' => [
                                'currentPage' => ['type' => 'integer'],
                                'lastPage' => ['type' => 'integer'],
                                'perPage' => ['type' => 'integer'],
                                'total' => ['type' => 'integer'],
                                'from' => ['type' => 'integer', 'nullable' => true],
                                'to' => ['type' => 'integer', 'nullable' => true],
                            ],
                        ],
                    ],
                ],
            ],
            'common' => [
                'uuid' => [
                    'type' => 'string',
                    'format' => 'uuid',
                    'example' => '550e8400-e29b-41d4-a716-446655440000',
                ],
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'example' => 'user@example.com',
                ],
                'datetime' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => '2026-01-20T10:00:00.000000Z',
                ],
                'date' => [
                    'type' => 'string',
                    'format' => 'date',
                    'example' => '2026-01-20',
                ],
                'url' => [
                    'type' => 'string',
                    'format' => 'uri',
                    'example' => 'https://example.com',
                ],
            ],
        ]);
    }

    /**
     * GET /api/meta/auth
     * 
     * Returns authentication documentation.
     */
    public function auth(): JsonResponse
    {
        return ApiResponse::success([
            'type' => 'Bearer Token (Laravel Sanctum)',
            'header' => 'Authorization',
            'format' => 'Bearer {token}',
            'obtaining' => [
                'user' => [
                    'endpoint' => '/api/auth/login',
                    'method' => 'POST',
                    'body' => ['email' => 'string', 'password' => 'string'],
                    'response' => ['token' => 'string', 'expiresAt' => 'datetime'],
                ],
                'admin' => [
                    'endpoint' => '/api/admin/login',
                    'method' => 'POST',
                    'body' => ['email' => 'string', 'password' => 'string'],
                    'response' => ['token' => 'string', 'expiresAt' => 'datetime'],
                ],
            ],
            'guards' => [
                'sanctum' => [
                    'description' => 'User authentication',
                    'tokenLifetime' => '7 days',
                    'refreshEndpoint' => '/api/auth/refresh',
                ],
                'admin' => [
                    'description' => 'Admin authentication',
                    'tokenLifetime' => '1 day',
                    'refreshEndpoint' => '/api/admin/refresh',
                ],
            ],
            'roles' => [
                'user' => 'Standard user (public registration)',
                'viewer' => 'Admin with view-only access',
                'editor' => 'Admin with create/edit access',
                'admin' => 'Full admin access',
                'super_admin' => 'Super administrator with all permissions',
            ],
            'roleHierarchy' => 'super_admin > admin > editor > viewer > user',
            'usage' => [
                'step1' => 'Call login endpoint with credentials',
                'step2' => 'Store the returned token securely',
                'step3' => 'Include token in Authorization header for protected endpoints',
                'step4' => 'Refresh token before expiration',
            ],
        ]);
    }

    /**
     * GET /api/meta/openapi or /api/{version}/meta/openapi
     * 
     * Returns OpenAPI 3.0 specification.
     */
    public function openapi(Request $request, ?string $version = null): JsonResponse
    {
        $version = $this->resolveVersion($request, $version);
        $cacheKey = "api_openapi_{$version}";
        $cacheTtl = config('api.documentation.cache_ttl', 3600);

        $spec = Cache::remember($cacheKey, $cacheTtl, function () use ($version) {
            return $this->generateOpenApiSpec($version);
        });

        $response = response()->json($spec);
        
        return $this->addVersionHeaders($response, $version);
    }

    /**
     * Generate OpenAPI 3.0 specification.
     */
    protected function generateOpenApiSpec(string $version = 'v1'): array
    {
        $endpoints = ApiRegistry::grouped($version);
        $paths = [];

        foreach ($endpoints as $group => $groupEndpoints) {
            foreach ($groupEndpoints as $endpoint) {
                // Use versioned path if versioning is enabled
                $path = config('api.versioning.enabled', true)
                    ? preg_replace('#^/api#', '', $endpoint['path'])
                    : $endpoint['path'];
                    
                $method = strtolower($endpoint['method']);
                
                if (!isset($paths[$path])) {
                    $paths[$path] = [];
                }

                $operation = [
                    'summary' => $endpoint['name'],
                    'description' => $endpoint['description'],
                    'tags' => $endpoint['tags'] ?? [$group],
                    'operationId' => $this->generateOperationId($endpoint),
                    'responses' => [
                        '200' => [
                            'description' => 'Successful response',
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'properties' => [
                                            'success' => ['type' => 'boolean'],
                                            'data' => $endpoint['response'] ?? ['type' => 'object'],
                                            'error' => ['type' => 'null'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];

                // Add deprecation flag
                if (!empty($endpoint['deprecated'])) {
                    $operation['deprecated'] = true;
                    if (!empty($endpoint['deprecation']['message'])) {
                        $operation['description'] .= "\n\n**⚠️ Deprecated:** " . $endpoint['deprecation']['message'];
                    }
                    if (!empty($endpoint['deprecation']['replacedBy'])) {
                        $operation['description'] .= "\n\n**Replacement:** `" . $endpoint['deprecation']['replacedBy'] . "`";
                    }
                }

                if ($endpoint['authentication']['required'] ?? false) {
                    $operation['security'] = [['bearerAuth' => []]];
                }

                if (isset($endpoint['requestBody'])) {
                    $operation['requestBody'] = [
                        'required' => true,
                        'content' => [
                            'application/json' => [
                                'schema' => $this->convertSchemaToOpenApi($endpoint['requestBody']),
                            ],
                        ],
                    ];
                }

                if (isset($endpoint['queryParams'])) {
                    $operation['parameters'] = [];
                    foreach ($endpoint['queryParams']['properties'] ?? [] as $name => $prop) {
                        $operation['parameters'][] = [
                            'name' => $name,
                            'in' => 'query',
                            'required' => $prop['required'] ?? false,
                            'schema' => ['type' => $this->normalizeType($prop['type'] ?? 'string')],
                            'description' => $prop['description'] ?? null,
                        ];
                    }
                }

                if (isset($endpoint['pathParams'])) {
                    if (!isset($operation['parameters'])) {
                        $operation['parameters'] = [];
                    }
                    foreach ($endpoint['pathParams']['properties'] ?? [] as $name => $prop) {
                        $operation['parameters'][] = [
                            'name' => $name,
                            'in' => 'path',
                            'required' => true,
                            'schema' => ['type' => $this->normalizeType($prop['type'] ?? 'string')],
                            'description' => $prop['description'] ?? null,
                        ];
                    }
                }

                // Add error responses
                if (isset($endpoint['errorCodes'])) {
                    foreach ($endpoint['errorCodes'] as $errorCode) {
                        $httpStatus = ErrorCode::getHttpStatus($errorCode);
                        if (!isset($operation['responses'][$httpStatus])) {
                            $operation['responses'][$httpStatus] = [
                                'description' => ErrorCode::getDescription($errorCode),
                                'content' => [
                                    'application/json' => [
                                        'schema' => [
                                            '$ref' => '#/components/schemas/ErrorResponse',
                                        ],
                                    ],
                                ],
                            ];
                        }
                    }
                }

                $paths[$path][$method] = $operation;
            }
        }

        $baseUrl = config('api.versioning.enabled', true)
            ? url("/api/{$version}")
            : url('/api');

        return [
            'openapi' => '3.0.3',
            'info' => [
                'title' => config('app.name', 'Smatatech') . ' API',
                'description' => "Self-documenting RESTful API - Version {$version}",
                'version' => $version,
                'contact' => [
                    'email' => config('api.documentation.contact_email', config('mail.from.address', 'api@example.com')),
                ],
            ],
            'servers' => [
                ['url' => $baseUrl, 'description' => ucfirst(app()->environment()) . ' server'],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum Token',
                        'description' => 'Enter your Bearer token in the format: Bearer {token}',
                    ],
                ],
                'schemas' => [
                    'ErrorResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => false],
                            'data' => ['type' => 'null'],
                            'error' => [
                                'type' => 'object',
                                'properties' => [
                                    'code' => ['type' => 'string', 'example' => 'VALIDATION_ERROR'],
                                    'message' => ['type' => 'string', 'example' => 'The given data was invalid.'],
                                    'details' => ['type' => 'object', 'nullable' => true],
                                ],
                            ],
                        ],
                    ],
                    'SuccessResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'data' => ['type' => 'object'],
                            'error' => ['type' => 'null'],
                            'message' => ['type' => 'string', 'nullable' => true],
                        ],
                    ],
                    'PaginatedResponse' => [
                        'type' => 'object',
                        'properties' => [
                            'success' => ['type' => 'boolean', 'example' => true],
                            'data' => ['type' => 'array', 'items' => ['type' => 'object']],
                            'error' => ['type' => 'null'],
                            'meta' => [
                                'type' => 'object',
                                'properties' => [
                                    'currentPage' => ['type' => 'integer'],
                                    'lastPage' => ['type' => 'integer'],
                                    'perPage' => ['type' => 'integer'],
                                    'total' => ['type' => 'integer'],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'paths' => $paths,
            'tags' => $this->generateTags($endpoints),
        ];
    }

    /**
     * Generate operation ID from endpoint info.
     */
    protected function generateOperationId(array $endpoint): string
    {
        $method = strtolower($endpoint['method']);
        $path = $endpoint['path'];
        
        // Convert path to camelCase operation ID
        $parts = explode('/', trim($path, '/'));
        $parts = array_filter($parts, fn($p) => $p !== 'api' && !str_starts_with($p, '{'));
        
        $operationId = $method;
        foreach ($parts as $part) {
            $operationId .= ucfirst($part);
        }
        
        return $operationId;
    }

    /**
     * Convert internal schema to OpenAPI format.
     */
    protected function convertSchemaToOpenApi(array $schema): array
    {
        $result = [
            'type' => $schema['type'] ?? 'object',
        ];

        if (isset($schema['properties'])) {
            $result['properties'] = [];
            $required = [];

            foreach ($schema['properties'] as $name => $prop) {
                $result['properties'][$name] = [
                    'type' => $this->normalizeType($prop['type'] ?? 'string'),
                ];
                
                if (isset($prop['description'])) {
                    $result['properties'][$name]['description'] = $prop['description'];
                }
                
                if (isset($prop['example'])) {
                    $result['properties'][$name]['example'] = $prop['example'];
                }

                if ($prop['required'] ?? false) {
                    $required[] = $name;
                }
            }

            if (!empty($required)) {
                $result['required'] = $required;
            }
        }

        return $result;
    }

    /**
     * Normalize type to OpenAPI type.
     */
    protected function normalizeType(string $type): string
    {
        return match ($type) {
            'int', 'integer' => 'integer',
            'float', 'double', 'number' => 'number',
            'bool', 'boolean' => 'boolean',
            'uuid', 'email', 'url', 'datetime', 'date' => 'string',
            default => $type,
        };
    }

    /**
     * Generate tags from endpoint groups.
     */
    protected function generateTags(array $groups): array
    {
        $tags = [];
        
        foreach (array_keys($groups) as $group) {
            $tags[] = [
                'name' => $group,
                'description' => ucfirst(str_replace(['-', '_'], ' ', $group)) . ' endpoints',
            ];
        }

        return $tags;
    }
}
