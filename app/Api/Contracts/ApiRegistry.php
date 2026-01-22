<?php

namespace App\Api\Contracts;

use App\Api\ErrorCode;

/**
 * API Registry - Single Source of Truth
 * 
 * All endpoint contracts are registered here.
 * This registry is used to generate documentation and validate requests.
 * Supports API versioning with version-aware endpoint filtering.
 */
class ApiRegistry
{
    protected static array $endpoints = [];
    protected static bool $initialized = false;
    protected static string $currentVersion = 'v1';

    /**
     * Register an endpoint contract.
     */
    public static function register(EndpointContract $contract): void
    {
        $key = $contract->method . ':' . $contract->path;
        self::$endpoints[$key] = $contract;
    }

    /**
     * Set the current API version context.
     */
    public static function setVersion(string $version): void
    {
        self::$currentVersion = $version;
    }

    /**
     * Get the current API version.
     */
    public static function getVersion(): string
    {
        return self::$currentVersion;
    }

    /**
     * Get supported API versions.
     */
    public static function getSupportedVersions(): array
    {
        return config('api.versioning.supported', ['v1']);
    }

    /**
     * Get all registered endpoints.
     */
    public static function all(?string $version = null): array
    {
        self::initialize();
        
        if ($version === null || !config('api.versioning.enabled', true)) {
            return self::$endpoints;
        }

        // Filter endpoints by version
        return array_filter(self::$endpoints, function ($endpoint) use ($version) {
            return $endpoint->isAvailableIn($version);
        });
    }

    /**
     * Get endpoints grouped by resource.
     */
    public static function grouped(?string $version = null): array
    {
        self::initialize();
        $version = $version ?? self::$currentVersion;
        $groups = [];
        
        $endpoints = self::all($version);
        
        foreach ($endpoints as $endpoint) {
            $group = $endpoint->group ?? 'default';
            if (!isset($groups[$group])) {
                $groups[$group] = [];
            }
            
            $endpointArray = $endpoint->toArray();
            
            // Add deprecation warning if applicable
            if ($endpoint->isDeprecatedIn($version)) {
                $endpointArray['deprecated'] = true;
            }
            
            $groups[$group][] = $endpointArray;
        }

        ksort($groups);
        return $groups;
    }

    /**
     * Get endpoint by method and path.
     */
    public static function get(string $method, string $path): ?EndpointContract
    {
        self::initialize();
        $key = strtoupper($method) . ':' . $path;
        return self::$endpoints[$key] ?? null;
    }

    /**
     * Get full API specification.
     */
    public static function getSpec(?string $version = null): array
    {
        self::initialize();
        $version = $version ?? self::$currentVersion;
        
        $spec = [
            'apiVersion' => $version,
            'specVersion' => '1.0.0',
            'title' => config('app.name', 'Smatatech') . ' API',
            'description' => 'Self-documenting RESTful API',
            'baseUrl' => config('api.versioning.enabled', true) 
                ? url("/api/{$version}") 
                : url('/api'),
            'generatedAt' => now()->toIso8601String(),
            'versioning' => self::getVersioningSpec($version),
            'responseFormat' => self::getResponseFormat(),
            'errorCodes' => ErrorCode::all(),
            'authentication' => self::getAuthSpec(),
            'rateLimit' => self::getRateLimitSpec(),
            'endpoints' => self::grouped($version),
        ];

        return $spec;
    }

    /**
     * Get versioning specification.
     */
    protected static function getVersioningSpec(string $currentVersion): array
    {
        $supported = self::getSupportedVersions();
        
        return [
            'current' => $currentVersion,
            'supported' => $supported,
            'default' => config('api.versioning.default', 'v1'),
            'latest' => end($supported) ?: 'v1',
            'header' => config('api.versioning.header', 'X-API-Version'),
            'urlPattern' => '/api/{version}/*',
        ];
    }

    protected static function getResponseFormat(): array
    {
        return [
            'success' => [
                'description' => 'All successful responses',
                'schema' => [
                    'success' => ['type' => 'boolean', 'value' => true],
                    'data' => ['type' => 'any', 'description' => 'Response data'],
                    'error' => ['type' => 'null'],
                    'meta' => ['type' => 'object', 'optional' => true],
                    'message' => ['type' => 'string', 'optional' => true],
                ],
            ],
            'error' => [
                'description' => 'All error responses',
                'schema' => [
                    'success' => ['type' => 'boolean', 'value' => false],
                    'data' => ['type' => 'null'],
                    'error' => [
                        'type' => 'object',
                        'properties' => [
                            'code' => ['type' => 'string', 'description' => 'Error code'],
                            'message' => ['type' => 'string', 'description' => 'Error message'],
                            'details' => ['type' => 'any', 'description' => 'Additional details'],
                        ],
                    ],
                ],
            ],
        ];
    }

    protected static function getAuthSpec(): array
    {
        return [
            'type' => 'Bearer Token',
            'header' => 'Authorization',
            'format' => 'Bearer {token}',
            'guards' => [
                'sanctum' => [
                    'description' => 'User authentication',
                    'tokenLifetime' => '7 days',
                    'obtainFrom' => '/api/auth/login',
                ],
                'admin' => [
                    'description' => 'Admin authentication',
                    'tokenLifetime' => '1 day',
                    'obtainFrom' => '/api/admin/login',
                ],
            ],
            'roles' => [
                'user' => 'Standard user',
                'viewer' => 'Admin with view-only access',
                'editor' => 'Admin with edit access',
                'admin' => 'Full admin access',
                'super_admin' => 'Super administrator',
            ],
        ];
    }

    protected static function getRateLimitSpec(): array
    {
        return [
            'default' => '60 requests per minute',
            'auth' => '5 requests per minute',
            'contact' => '5 requests per minute',
            'admin' => '60 requests per minute',
        ];
    }

    /**
     * Initialize all endpoint contracts.
     */
    protected static function initialize(): void
    {
        if (self::$initialized) {
            return;
        }

        self::registerPublicEndpoints();
        self::registerAuthEndpoints();
        self::registerAdminEndpoints();
        self::registerAiEndpoints();

        self::$initialized = true;
    }

    protected static function registerPublicEndpoints(): void
    {
        // Defined in PublicContracts class
        PublicContracts::register();
    }

    protected static function registerAuthEndpoints(): void
    {
        // Defined in AuthContracts class
        AuthContracts::register();
    }

    protected static function registerAdminEndpoints(): void
    {
        // Defined in AdminContracts class
        AdminContracts::register();
    }

    protected static function registerAiEndpoints(): void
    {
        // Defined in AiContracts class
        AiContracts::register();
    }
}
