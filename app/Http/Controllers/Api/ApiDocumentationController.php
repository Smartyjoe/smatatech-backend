<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use Illuminate\Http\JsonResponse;

class ApiDocumentationController extends BaseApiController
{
    /**
     * API Index - Self-describing API guide.
     * GET /api
     */
    public function index(): JsonResponse
    {
        return ApiResponse::success([
            'name' => config('app.name', 'Smatatech API'),
            'version' => '1.0.0',
            'description' => 'RESTful API for Smatatech Technologies platform',
            'documentation' => url('/api/docs'),
            'meta' => url('/api/meta'),
            'health' => url('/api/health'),
            'status' => 'operational',
            'timestamp' => now()->toIso8601String(),
            'responseFormat' => [
                'description' => 'All responses follow a standardized format',
                'success' => ['success' => true, 'data' => '...', 'error' => null],
                'error' => ['success' => false, 'data' => null, 'error' => ['code' => '...', 'message' => '...', 'details' => '...']],
                'fullSpec' => url('/api/meta/schemas'),
            ],
            'endpoints' => $this->getEndpointsSummary(),
            'authentication' => $this->getAuthInfo(),
            'headers' => $this->getRequiredHeaders(),
            'errorCodes' => url('/api/meta/errors'),
            'rateLimit' => [
                'requests' => 60,
                'perMinutes' => 1,
            ],
        ]);
    }

    /**
     * Full API Documentation.
     * GET /api/docs
     */
    public function docs(): JsonResponse
    {
        return ApiResponse::success([
            'title' => config('app.name', 'Smatatech') . ' API Documentation',
            'version' => '1.0.0',
            'baseUrl' => url('/api'),
            'description' => 'Complete API documentation for developers integrating with Smatatech platform.',
            'lastUpdated' => '2026-01-21',
            'meta' => [
                'fullSpec' => url('/api/meta'),
                'endpoints' => url('/api/meta/endpoints'),
                'errors' => url('/api/meta/errors'),
                'schemas' => url('/api/meta/schemas'),
                'auth' => url('/api/meta/auth'),
                'openapi' => url('/api/meta/openapi'),
            ],
            'authentication' => $this->getDetailedAuthDocs(),
            'headers' => $this->getDetailedHeadersDocs(),
            'responseFormat' => $this->getResponseFormatDocs(),
            'errorCodes' => ErrorCode::all(),
            'endpoints' => $this->getDetailedEndpointsDocs(),
            'examples' => $this->getExampleRequests(),
            'integration' => $this->getIntegrationGuide(),
        ]);
    }

    private function getEndpointsSummary(): array
    {
        return [
            'public' => [
                'GET /api' => 'API information and endpoint index',
                'GET /api/docs' => 'Full API documentation',
                'GET /api/meta' => 'Complete API specification (machine-readable)',
                'GET /api/health' => 'Health check endpoint',
                'GET /api/settings' => 'Public site settings',
                'GET /api/services' => 'List published services',
                'GET /api/services/{slug}' => 'Get service by slug',
                'GET /api/posts' => 'List published blog posts',
                'GET /api/posts/{slug}' => 'Get blog post by slug',
                'GET /api/posts/{slug}/related' => 'Get related posts',
                'GET /api/categories' => 'List blog categories',
                'GET /api/case-studies' => 'List published case studies',
                'GET /api/case-studies/{slug}' => 'Get case study by slug',
                'GET /api/case-studies/{slug}/related' => 'Get related case studies',
                'GET /api/testimonials' => 'List published testimonials',
                'GET /api/brands' => 'List active brands',
                'POST /api/contact' => 'Submit contact form',
                'GET /api/chatbot/config' => 'Get chatbot configuration',
                'POST /api/chat' => 'Send chat message',
                'POST /api/newsletter/subscribe' => 'Subscribe to newsletter',
                'POST /api/inquiries' => 'Submit service inquiry',
            ],
            'auth' => [
                'POST /api/auth/register' => 'User registration',
                'POST /api/auth/login' => 'User login',
                'POST /api/auth/logout' => 'User logout (authenticated)',
                'GET /api/auth/me' => 'Get current user (authenticated)',
                'POST /api/auth/refresh' => 'Refresh token (authenticated)',
                'POST /api/auth/forgot-password' => 'Request password reset',
                'POST /api/auth/reset-password' => 'Reset password with token',
            ],
            'admin' => [
                'POST /api/admin/login' => 'Admin login',
                'POST /api/admin/logout' => 'Admin logout',
                'GET /api/admin/me' => 'Get current admin',
                'GET /api/admin/dashboard/*' => 'Dashboard endpoints',
                'CRUD /api/admin/posts' => 'Blog post management',
                'CRUD /api/admin/categories' => 'Category management',
                'CRUD /api/admin/services' => 'Service management',
                'CRUD /api/admin/case-studies' => 'Case study management',
                'CRUD /api/admin/testimonials' => 'Testimonial management',
                'CRUD /api/admin/brands' => 'Brand management',
                'CRUD /api/admin/contacts' => 'Contact management',
                'CRUD /api/admin/users' => 'User management',
                '/api/admin/settings' => 'Site settings management',
                '/api/admin/chatbot/*' => 'Chatbot configuration',
                '/api/admin/email/*' => 'Email settings',
            ],
            'ai' => [
                'GET /api/ai/tools' => 'List AI tools (authenticated)',
                'GET /api/ai/credits' => 'Get credit balance',
                'POST /api/ai/credits/purchase' => 'Purchase credits',
                'GET /api/ai/usage' => 'Usage history',
                'POST /api/ai/tools/{id}/execute' => 'Execute AI tool',
            ],
        ];
    }

    private function getAuthInfo(): array
    {
        return [
            'type' => 'Bearer Token',
            'header' => 'Authorization: Bearer {token}',
            'tokenLifetime' => [
                'user' => '7 days',
                'admin' => '1 day',
            ],
            'endpoints' => [
                'user' => '/api/auth/login',
                'admin' => '/api/admin/login',
            ],
            'documentation' => url('/api/meta/auth'),
        ];
    }

    private function getRequiredHeaders(): array
    {
        return [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'Authorization' => 'Bearer {token} (for protected endpoints)',
        ];
    }

    private function getDetailedAuthDocs(): array
    {
        return [
            'overview' => 'The API uses Bearer token authentication via Laravel Sanctum.',
            'flows' => [
                'user' => [
                    'description' => 'Public user authentication for AI tools and commenting',
                    'register' => [
                        'endpoint' => 'POST /api/auth/register',
                        'body' => ['name' => 'string', 'email' => 'string', 'password' => 'string', 'password_confirmation' => 'string'],
                        'response' => ['user' => 'object', 'token' => 'string', 'expiresAt' => 'ISO8601 datetime'],
                    ],
                    'login' => [
                        'endpoint' => 'POST /api/auth/login',
                        'body' => ['email' => 'string', 'password' => 'string'],
                        'response' => ['user' => 'object', 'token' => 'string', 'expiresAt' => 'ISO8601 datetime'],
                    ],
                ],
                'admin' => [
                    'description' => 'Admin authentication for CMS access',
                    'login' => [
                        'endpoint' => 'POST /api/admin/login',
                        'body' => ['email' => 'string', 'password' => 'string'],
                        'response' => ['user' => 'object', 'token' => 'string', 'expiresAt' => 'ISO8601 datetime'],
                    ],
                    'roles' => ['super_admin', 'admin', 'editor', 'viewer'],
                ],
            ],
            'tokenUsage' => 'Include token in Authorization header: Bearer {token}',
            'tokenRefresh' => 'POST /api/auth/refresh or /api/admin/refresh',
        ];
    }

    private function getDetailedHeadersDocs(): array
    {
        return [
            'required' => [
                'Content-Type' => [
                    'value' => 'application/json',
                    'description' => 'Required for all POST/PUT/PATCH requests',
                ],
                'Accept' => [
                    'value' => 'application/json',
                    'description' => 'Ensures JSON responses',
                ],
            ],
            'authentication' => [
                'Authorization' => [
                    'value' => 'Bearer {token}',
                    'description' => 'Required for protected endpoints',
                ],
            ],
        ];
    }

    private function getResponseFormatDocs(): array
    {
        return [
            'success' => [
                'structure' => [
                    'success' => 'boolean (true)',
                    'data' => 'any | null',
                    'error' => 'null',
                    'message' => 'string (optional)',
                    'meta' => 'object (for paginated responses)',
                ],
                'example' => [
                    'success' => true,
                    'data' => ['id' => 'uuid', 'name' => 'Example'],
                    'error' => null,
                    'message' => 'Resource created successfully',
                ],
            ],
            'error' => [
                'structure' => [
                    'success' => 'boolean (false)',
                    'data' => 'null',
                    'error' => [
                        'code' => 'string (from ErrorCode constants)',
                        'message' => 'string (human-readable)',
                        'details' => 'any | null (validation errors or additional info)',
                    ],
                ],
                'example' => [
                    'success' => false,
                    'data' => null,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'The given data was invalid.',
                        'details' => ['email' => ['The email field is required.']],
                    ],
                ],
            ],
            'paginated' => [
                'structure' => [
                    'success' => 'boolean (true)',
                    'data' => 'array',
                    'error' => 'null',
                    'meta' => [
                        'currentPage' => 'integer',
                        'lastPage' => 'integer',
                        'perPage' => 'integer',
                        'total' => 'integer',
                        'from' => 'integer|null',
                        'to' => 'integer|null',
                    ],
                ],
            ],
        ];
    }

    private function getDetailedEndpointsDocs(): array
    {
        return [
            'public' => $this->getPublicEndpointsDocs(),
            'auth' => $this->getAuthEndpointsDocs(),
            'admin' => ['see' => url('/api/meta/endpoints')],
            'ai' => $this->getAiEndpointsDocs(),
        ];
    }

    private function getPublicEndpointsDocs(): array
    {
        return [
            'services' => [
                'list' => ['method' => 'GET', 'path' => '/api/services', 'auth' => false],
                'show' => ['method' => 'GET', 'path' => '/api/services/{slug}', 'auth' => false],
            ],
            'posts' => [
                'list' => ['method' => 'GET', 'path' => '/api/posts', 'auth' => false, 'queryParams' => ['per_page', 'page', 'category', 'search']],
                'show' => ['method' => 'GET', 'path' => '/api/posts/{slug}', 'auth' => false],
                'related' => ['method' => 'GET', 'path' => '/api/posts/{slug}/related', 'auth' => false],
            ],
            'categories' => [
                'list' => ['method' => 'GET', 'path' => '/api/categories', 'auth' => false],
            ],
            'caseStudies' => [
                'list' => ['method' => 'GET', 'path' => '/api/case-studies', 'auth' => false],
                'show' => ['method' => 'GET', 'path' => '/api/case-studies/{slug}', 'auth' => false],
                'related' => ['method' => 'GET', 'path' => '/api/case-studies/{slug}/related', 'auth' => false],
            ],
            'contact' => [
                'submit' => ['method' => 'POST', 'path' => '/api/contact', 'auth' => false, 'rateLimit' => '5/minute'],
            ],
            'chatbot' => [
                'config' => ['method' => 'GET', 'path' => '/api/chatbot/config', 'auth' => false],
                'chat' => ['method' => 'POST', 'path' => '/api/chat', 'auth' => false, 'rateLimit' => '10/minute'],
            ],
            'newsletter' => [
                'subscribe' => ['method' => 'POST', 'path' => '/api/newsletter/subscribe', 'auth' => false],
                'unsubscribe' => ['method' => 'POST', 'path' => '/api/newsletter/unsubscribe', 'auth' => false],
            ],
            'inquiries' => [
                'submit' => ['method' => 'POST', 'path' => '/api/inquiries', 'auth' => false],
            ],
        ];
    }

    private function getAuthEndpointsDocs(): array
    {
        return [
            'register' => ['method' => 'POST', 'path' => '/api/auth/register', 'auth' => false],
            'login' => ['method' => 'POST', 'path' => '/api/auth/login', 'auth' => false],
            'logout' => ['method' => 'POST', 'path' => '/api/auth/logout', 'auth' => true],
            'me' => ['method' => 'GET', 'path' => '/api/auth/me', 'auth' => true],
            'refresh' => ['method' => 'POST', 'path' => '/api/auth/refresh', 'auth' => true],
            'forgotPassword' => ['method' => 'POST', 'path' => '/api/auth/forgot-password', 'auth' => false],
            'resetPassword' => ['method' => 'POST', 'path' => '/api/auth/reset-password', 'auth' => false],
        ];
    }

    private function getAiEndpointsDocs(): array
    {
        return [
            'tools' => ['method' => 'GET', 'path' => '/api/ai/tools', 'auth' => true],
            'credits' => ['method' => 'GET', 'path' => '/api/ai/credits', 'auth' => true],
            'purchase' => ['method' => 'POST', 'path' => '/api/ai/credits/purchase', 'auth' => true],
            'usage' => ['method' => 'GET', 'path' => '/api/ai/usage', 'auth' => true],
            'execute' => ['method' => 'POST', 'path' => '/api/ai/tools/{id}/execute', 'auth' => true],
        ];
    }

    private function getExampleRequests(): array
    {
        return [
            'login' => [
                'request' => [
                    'method' => 'POST',
                    'url' => '/api/auth/login',
                    'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                    'body' => ['email' => 'user@example.com', 'password' => 'your-password'],
                ],
                'response' => [
                    'success' => true,
                    'data' => [
                        'user' => ['id' => '...', 'name' => 'John Doe', 'email' => 'user@example.com'],
                        'token' => 'your-bearer-token',
                        'expiresAt' => '2026-01-27T00:00:00.000000Z',
                    ],
                    'error' => null,
                ],
            ],
            'errorExample' => [
                'request' => [
                    'method' => 'POST',
                    'url' => '/api/auth/login',
                    'body' => ['email' => 'invalid'],
                ],
                'response' => [
                    'success' => false,
                    'data' => null,
                    'error' => [
                        'code' => 'VALIDATION_ERROR',
                        'message' => 'The given data was invalid.',
                        'details' => [
                            'email' => ['The email must be a valid email address.'],
                            'password' => ['The password field is required.'],
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getIntegrationGuide(): array
    {
        return [
            'overview' => 'This API can be consumed by any frontend framework (React, Vue, Next.js, etc.) or mobile application.',
            'steps' => [
                '1. Review API Spec' => 'Visit /api/meta for complete specification',
                '2. Authentication' => 'Call login endpoint to receive Bearer token',
                '3. Store Token' => 'Store token securely (httpOnly cookie recommended for web)',
                '4. Make Requests' => 'Include token in Authorization header for protected routes',
                '5. Handle Errors' => 'Check error.code field and handle appropriately',
            ],
            'errorHandling' => [
                'Always check the success field first',
                'Use error.code for programmatic handling',
                'Display error.message to users',
                'Use error.details for form validation errors',
            ],
            'security' => [
                'Always use HTTPS in production',
                'Store tokens securely',
                'Implement token refresh before expiration',
                'Never expose admin tokens in client-side code',
            ],
        ];
    }
}
