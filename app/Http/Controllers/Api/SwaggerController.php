<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Swagger UI Controller
 * 
 * Serves the Swagger UI documentation interface.
 */
class SwaggerController extends Controller
{
    /**
     * Display Swagger UI documentation.
     * 
     * GET /api/swagger
     * GET /api/v1/swagger
     * GET /api/v2/swagger
     */
    public function index(Request $request, ?string $version = null): View
    {
        // Check if Swagger is enabled
        if (!config('api.swagger.enabled', true)) {
            abort(404, 'API documentation is not available');
        }

        // Determine version
        $version = $version ?? $this->resolveVersion($request);
        $supportedVersions = config('api.versioning.supported', ['v1']);
        
        if (!in_array($version, $supportedVersions)) {
            $version = config('api.versioning.default', 'v1');
        }

        // Build OpenAPI URL based on version
        $openApiUrl = $this->getOpenApiUrl($version);

        return view('api.swagger', [
            'version' => $version,
            'openApiUrl' => $openApiUrl,
            'supportedVersions' => $supportedVersions,
        ]);
    }

    /**
     * Resolve API version from request.
     */
    protected function resolveVersion(Request $request): string
    {
        // Check URL path first
        $path = $request->path();
        if (preg_match('/api\/(v\d+)/', $path, $matches)) {
            return $matches[1];
        }

        // Check header
        $headerVersion = $request->header(config('api.versioning.header', 'X-API-Version'));
        if ($headerVersion && preg_match('/^v?\d+$/', $headerVersion)) {
            return str_starts_with($headerVersion, 'v') ? $headerVersion : 'v' . $headerVersion;
        }

        // Check query parameter
        $queryVersion = $request->query('version');
        if ($queryVersion && preg_match('/^v?\d+$/', $queryVersion)) {
            return str_starts_with($queryVersion, 'v') ? $queryVersion : 'v' . $queryVersion;
        }

        return config('api.versioning.default', 'v1');
    }

    /**
     * Get OpenAPI spec URL for a version.
     */
    protected function getOpenApiUrl(string $version): string
    {
        if (config('api.versioning.enabled', true)) {
            return url("/api/{$version}/meta/openapi");
        }

        return url('/api/meta/openapi');
    }
}
