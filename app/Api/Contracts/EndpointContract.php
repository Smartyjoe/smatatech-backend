<?php

namespace App\Api\Contracts;

/**
 * Endpoint Contract Definition
 * 
 * Defines the complete specification for an API endpoint including:
 * - Method, path, name, description
 * - Authentication requirements
 * - Request schema (body, query, params)
 * - Response schema
 * - Possible error codes
 * - API versioning support
 */
class EndpointContract
{
    public string $name;
    public string $method;
    public string $path;
    public string $description;
    public string $group;
    public bool $auth = false;
    public ?string $guard = null;
    public ?string $role = null;
    public array $permissions = [];
    public ?Schema $requestBody = null;
    public ?Schema $queryParams = null;
    public ?Schema $pathParams = null;
    public ?Schema $response = null;
    public array $errorCodes = [];
    public array $headers = [];
    public ?string $rateLimit = null;
    public array $tags = [];
    public array $examples = [];
    
    // Versioning support
    public array $versions = ['v1']; // Supported versions
    public ?string $introducedIn = 'v1'; // Version when endpoint was introduced
    public ?string $deprecatedIn = null; // Version when endpoint was deprecated
    public ?string $removedIn = null; // Version when endpoint will be/was removed
    public ?string $deprecationMessage = null; // Custom deprecation message
    public ?string $replacedBy = null; // Replacement endpoint path

    public function __construct(string $method, string $path, string $name)
    {
        $this->method = strtoupper($method);
        $this->path = $path;
        $this->name = $name;
        $this->description = $name;
    }

    public static function get(string $path, string $name): self
    {
        return new self('GET', $path, $name);
    }

    public static function post(string $path, string $name): self
    {
        return new self('POST', $path, $name);
    }

    public static function put(string $path, string $name): self
    {
        return new self('PUT', $path, $name);
    }

    public static function patch(string $path, string $name): self
    {
        return new self('PATCH', $path, $name);
    }

    public static function delete(string $path, string $name): self
    {
        return new self('DELETE', $path, $name);
    }

    public function description(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function group(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function tags(string ...$tags): self
    {
        $this->tags = $tags;
        return $this;
    }

    public function public(): self
    {
        $this->auth = false;
        $this->guard = null;
        return $this;
    }

    public function auth(string $guard = 'sanctum'): self
    {
        $this->auth = true;
        $this->guard = $guard;
        return $this;
    }

    public function adminAuth(): self
    {
        $this->auth = true;
        $this->guard = 'admin';
        return $this;
    }

    public function role(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function permissions(string ...$permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function requestBody(Schema $schema): self
    {
        $this->requestBody = $schema;
        return $this;
    }

    public function queryParams(Schema $schema): self
    {
        $this->queryParams = $schema;
        return $this;
    }

    public function pathParams(Schema $schema): self
    {
        $this->pathParams = $schema;
        return $this;
    }

    public function response(Schema $schema): self
    {
        $this->response = $schema;
        return $this;
    }

    public function errors(string ...$errorCodes): self
    {
        $this->errorCodes = $errorCodes;
        return $this;
    }

    public function headers(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }

    public function rateLimit(string $limit): self
    {
        $this->rateLimit = $limit;
        return $this;
    }

    public function example(string $name, array $request, array $response): self
    {
        $this->examples[$name] = [
            'request' => $request,
            'response' => $response,
        ];
        return $this;
    }

    /**
     * Set supported versions for this endpoint.
     */
    public function versions(string ...$versions): self
    {
        $this->versions = $versions;
        return $this;
    }

    /**
     * Set the version when this endpoint was introduced.
     */
    public function introducedIn(string $version): self
    {
        $this->introducedIn = $version;
        if (!in_array($version, $this->versions)) {
            $this->versions[] = $version;
        }
        return $this;
    }

    /**
     * Mark endpoint as deprecated.
     */
    public function deprecated(string $inVersion, ?string $message = null, ?string $replacedBy = null): self
    {
        $this->deprecatedIn = $inVersion;
        $this->deprecationMessage = $message;
        $this->replacedBy = $replacedBy;
        return $this;
    }

    /**
     * Mark endpoint as removed in a version.
     */
    public function removedIn(string $version): self
    {
        $this->removedIn = $version;
        return $this;
    }

    /**
     * Check if endpoint is available in a specific version.
     */
    public function isAvailableIn(string $version): bool
    {
        // Not available if removed in this or earlier version
        if ($this->removedIn && version_compare($version, $this->removedIn, '>=')) {
            return false;
        }

        // Not available if introduced after this version
        if ($this->introducedIn && version_compare($version, $this->introducedIn, '<')) {
            return false;
        }

        return in_array($version, $this->versions) || empty($this->versions);
    }

    /**
     * Check if endpoint is deprecated in a specific version.
     */
    public function isDeprecatedIn(string $version): bool
    {
        if (!$this->deprecatedIn) {
            return false;
        }

        return version_compare($version, $this->deprecatedIn, '>=');
    }

    /**
     * Get the versioned path for this endpoint.
     */
    public function getVersionedPath(string $version): string
    {
        // Replace /api/ with /api/{version}/
        return preg_replace('#^/api/#', "/api/{$version}/", $this->path);
    }

    /**
     * Convert to array for documentation.
     */
    public function toArray(): array
    {
        $result = [
            'name' => $this->name,
            'method' => $this->method,
            'path' => $this->path,
            'description' => $this->description,
            'group' => $this->group ?? 'default',
            'tags' => $this->tags,
            'authentication' => [
                'required' => $this->auth,
                'guard' => $this->guard,
                'role' => $this->role,
                'permissions' => $this->permissions,
            ],
            'versioning' => [
                'versions' => $this->versions,
                'introducedIn' => $this->introducedIn,
                'deprecatedIn' => $this->deprecatedIn,
                'removedIn' => $this->removedIn,
            ],
        ];

        // Add deprecation info if deprecated
        if ($this->deprecatedIn) {
            $result['deprecated'] = true;
            $result['deprecation'] = [
                'since' => $this->deprecatedIn,
                'message' => $this->deprecationMessage,
                'replacedBy' => $this->replacedBy,
            ];
        }

        if ($this->rateLimit) {
            $result['rateLimit'] = $this->rateLimit;
        }

        if (!empty($this->headers)) {
            $result['headers'] = $this->headers;
        }

        if ($this->pathParams) {
            $result['pathParams'] = $this->pathParams->toArray();
        }

        if ($this->queryParams) {
            $result['queryParams'] = $this->queryParams->toArray();
        }

        if ($this->requestBody) {
            $result['requestBody'] = $this->requestBody->toArray();
        }

        if ($this->response) {
            $result['response'] = $this->response->toArray();
        }

        if (!empty($this->errorCodes)) {
            $result['errorCodes'] = $this->errorCodes;
        }

        if (!empty($this->examples)) {
            $result['examples'] = $this->examples;
        }

        return $result;
    }

    /**
     * Get validation rules for this endpoint.
     */
    public function getValidationRules(): array
    {
        return $this->requestBody?->toValidationRules() ?? [];
    }
}
