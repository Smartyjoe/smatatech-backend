<?php

                    namespace App\Http\Controllers\Api;

                    use Illuminate\Http\JsonResponse;

                    class ApiDocumentationController extends BaseApiController
                    {
                        /**
                        * API Index - Self-describing API guide.
                        * GET /api
                        */
                        public function index(): JsonResponse
                        {
                            return $this->successResponse([
                                'name' => config('app.name', 'Smatatech API'),
                                'version' => '1.0.0',
                                'description' => 'RESTful API for Smatatech Technologies platform',
                                'documentation' => url('/api/docs'),
                                'health' => url('/api/health'),
                                'status' => 'operational',
                                'timestamp' => now()->toIso8601String(),
                                'endpoints' => $this->getEndpointsSummary(),
                                'authentication' => $this->getAuthInfo(),
                                'headers' => $this->getRequiredHeaders(),
                                'rateLimit' => [
                                    'requests' => 60,
                                    'perMinutes' => 1,
                                    'headers' => [
                                        'X-RateLimit-Limit' => 'Maximum requests allowed',
                                        'X-RateLimit-Remaining' => 'Requests remaining in window',
                                    ],
                                ],
                            ]);
                        }

                        /**
                        * Full API Documentation.
                        * GET /api/docs
                        */
                        public function docs(): JsonResponse
                        {
                            return $this->successResponse([
                                'title' => config('app.name', 'Smatatech') . ' API Documentation',
                                'version' => '1.0.0',
                                'baseUrl' => url('/api'),
                                'description' => 'Complete API documentation for developers integrating with Smatatech platform.',
                                'lastUpdated' => '2026-01-20',
                                'authentication' => $this->getDetailedAuthDocs(),
                                'headers' => $this->getDetailedHeadersDocs(),
                                'responseFormat' => $this->getResponseFormatDocs(),
                                'errorCodes' => $this->getErrorCodesDocs(),
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
                                    'GET /api/health' => 'Health check endpoint',
                                    'GET /api/settings' => 'Public site settings',
                                    'GET /api/services' => 'List published services',
                                    'GET /api/services/{slug}' => 'Get service by slug',
                                    'GET /api/posts' => 'List published blog posts',
                                    'GET /api/posts/{slug}' => 'Get blog post by slug',
                                    'GET /api/categories' => 'List blog categories',
                                    'GET /api/case-studies' => 'List published case studies',
                                    'GET /api/case-studies/{slug}' => 'Get case study by slug',
                                    'GET /api/testimonials' => 'List published testimonials',
                                    'GET /api/brands' => 'List active brands',
                                    'POST /api/contact' => 'Submit contact form',
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
                                'optional' => [
                                    'X-Requested-With' => [
                                        'value' => 'XMLHttpRequest',
                                        'description' => 'Identifies AJAX requests',
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
                                        'data' => 'object|array|null',
                                        'message' => 'string (optional)',
                                    ],
                                    'example' => [
                                        'success' => true,
                                        'data' => ['id' => 'uuid', 'name' => 'Example'],
                                        'message' => 'Resource created successfully',
                                    ],
                                ],
                                'paginated' => [
                                    'structure' => [
                                        'success' => 'boolean (true)',
                                        'data' => 'array',
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
                                'error' => [
                                    'structure' => [
                                        'success' => 'boolean (false)',
                                        'message' => 'string',
                                        'errors' => 'object (validation errors) or array',
                                    ],
                                    'example' => [
                                        'success' => false,
                                        'message' => 'The given data was invalid.',
                                        'errors' => ['email' => ['The email field is required.']],
                                    ],
                                ],
                            ];
                        }

                        private function getErrorCodesDocs(): array
                        {
                            return [
                                200 => ['status' => 'OK', 'description' => 'Request successful'],
                                201 => ['status' => 'Created', 'description' => 'Resource created successfully'],
                                204 => ['status' => 'No Content', 'description' => 'Request successful, no content to return'],
                                400 => ['status' => 'Bad Request', 'description' => 'Invalid request parameters'],
                                401 => ['status' => 'Unauthorized', 'description' => 'Authentication required or token invalid'],
                                402 => ['status' => 'Payment Required', 'description' => 'Insufficient credits'],
                                403 => ['status' => 'Forbidden', 'description' => 'Insufficient permissions'],
                                404 => ['status' => 'Not Found', 'description' => 'Resource not found'],
                                405 => ['status' => 'Method Not Allowed', 'description' => 'HTTP method not supported'],
                                422 => ['status' => 'Unprocessable Entity', 'description' => 'Validation errors'],
                                429 => ['status' => 'Too Many Requests', 'description' => 'Rate limit exceeded'],
                                500 => ['status' => 'Server Error', 'description' => 'Internal server error'],
                                501 => ['status' => 'Not Implemented', 'description' => 'Feature not yet available'],
                                503 => ['status' => 'Service Unavailable', 'description' => 'Service temporarily unavailable'],
                            ];
                        }

                        private function getDetailedEndpointsDocs(): array
                        {
                            return [
                                'public' => $this->getPublicEndpointsDocs(),
                                'auth' => $this->getAuthEndpointsDocs(),
                                'admin' => $this->getAdminEndpointsDocs(),
                                'ai' => $this->getAiEndpointsDocs(),
                            ];
                        }

                        private function getPublicEndpointsDocs(): array
                        {
                            return [
                                'services' => [
                                    'list' => [
                                        'method' => 'GET',
                                        'path' => '/api/services',
                                        'description' => 'Get all published services',
                                        'auth' => false,
                                        'response' => [
                                            'data' => [['id', 'title', 'slug', 'shortDescription', 'fullDescription', 'icon', 'image', 'status', 'order', 'createdAt', 'updatedAt']],
                                        ],
                                    ],
                                    'show' => [
                                        'method' => 'GET',
                                        'path' => '/api/services/{slug}',
                                        'description' => 'Get service by slug',
                                        'auth' => false,
                                        'params' => ['slug' => 'string (URL slug)'],
                                    ],
                                ],
                                'posts' => [
                                    'list' => [
                                        'method' => 'GET',
                                        'path' => '/api/posts',
                                        'description' => 'Get paginated published blog posts',
                                        'auth' => false,
                                        'queryParams' => [
                                            'per_page' => 'integer (1-50, default: 15)',
                                            'page' => 'integer',
                                            'category' => 'string (category slug)',
                                            'search' => 'string (search in title/content)',
                                        ],
                                    ],
                                    'show' => [
                                        'method' => 'GET',
                                        'path' => '/api/posts/{slug}',
                                        'description' => 'Get post by slug',
                                        'auth' => false,
                                    ],
                                ],
                                'categories' => [
                                    'list' => [
                                        'method' => 'GET',
                                        'path' => '/api/categories',
                                        'description' => 'Get all active categories with post counts',
                                        'auth' => false,
                                    ],
                                ],
                                'caseStudies' => [
                                    'list' => [
                                        'method' => 'GET',
                                        'path' => '/api/case-studies',
                                        'description' => 'Get paginated case studies',
                                        'auth' => false,
                                        'queryParams' => ['per_page' => 'integer (1-50)', 'page' => 'integer'],
                                    ],
                                    'show' => [
                                        'method' => 'GET',
                                        'path' => '/api/case-studies/{slug}',
                                        'auth' => false,
                                    ],
                                ],
                                'testimonials' => [
                                    'list' => [
                                        'method' => 'GET',
                                        'path' => '/api/testimonials',
                                        'auth' => false,
                                        'queryParams' => ['featured' => 'boolean (filter featured only)'],
                                    ],
                                ],
                                'brands' => [
                                    'list' => [
                                        'method' => 'GET',
                                        'path' => '/api/brands',
                                        'auth' => false,
                                    ],
                                ],
                                'settings' => [
                                    'get' => [
                                        'method' => 'GET',
                                        'path' => '/api/settings',
                                        'description' => 'Get public site settings',
                                        'auth' => false,
                                        'response' => ['siteName', 'siteDescription', 'contactEmail', 'contactPhone', 'address', 'socialLinks'],
                                    ],
                                ],
                                'contact' => [
                                    'submit' => [
                                        'method' => 'POST',
                                        'path' => '/api/contact',
                                        'description' => 'Submit contact form',
                                        'auth' => false,
                                        'body' => [
                                            'name' => 'string (required)',
                                            'email' => 'string (required)',
                                            'message' => 'string (required)',
                                            'company' => 'string (optional)',
                                            'phone' => 'string (optional)',
                                            'projectType' => 'string (optional)',
                                            'budget' => 'number (optional)',
                                            'services' => 'array (optional)',
                                        ],
                                        'rateLimit' => '5 requests per minute',
                                    ],
                                ],
                            ];
                        }

                        private function getAuthEndpointsDocs(): array
                        {
                            return [
                                'register' => [
                                    'method' => 'POST',
                                    'path' => '/api/auth/register',
                                    'description' => 'Register new user account',
                                    'auth' => false,
                                    'body' => [
                                        'name' => 'string (required)',
                                        'email' => 'string (required, unique)',
                                        'password' => 'string (required, min 8 chars)',
                                        'password_confirmation' => 'string (required)',
                                    ],
                                    'response' => ['user' => 'object', 'token' => 'string', 'expiresAt' => 'datetime'],
                                ],
                                'login' => [
                                    'method' => 'POST',
                                    'path' => '/api/auth/login',
                                    'description' => 'Login user',
                                    'auth' => false,
                                    'body' => ['email' => 'string', 'password' => 'string'],
                                    'response' => ['user' => 'object', 'token' => 'string', 'expiresAt' => 'datetime'],
                                ],
                                'logout' => [
                                    'method' => 'POST',
                                    'path' => '/api/auth/logout',
                                    'description' => 'Logout and revoke token',
                                    'auth' => true,
                                ],
                                'me' => [
                                    'method' => 'GET',
                                    'path' => '/api/auth/me',
                                    'description' => 'Get current authenticated user',
                                    'auth' => true,
                                ],
                                'refresh' => [
                                    'method' => 'POST',
                                    'path' => '/api/auth/refresh',
                                    'description' => 'Refresh authentication token',
                                    'auth' => true,
                                ],
                                'forgotPassword' => [
                                    'method' => 'POST',
                                    'path' => '/api/auth/forgot-password',
                                    'body' => ['email' => 'string'],
                                ],
                                'resetPassword' => [
                                    'method' => 'POST',
                                    'path' => '/api/auth/reset-password',
                                    'body' => ['token' => 'string', 'email' => 'string', 'password' => 'string', 'password_confirmation' => 'string'],
                                ],
                            ];
                        }

                        private function getAdminEndpointsDocs(): array
                        {
                            return [
                                'authentication' => [
                                    'login' => ['method' => 'POST', 'path' => '/api/admin/login', 'auth' => false],
                                    'logout' => ['method' => 'POST', 'path' => '/api/admin/logout', 'auth' => 'admin'],
                                    'me' => ['method' => 'GET', 'path' => '/api/admin/me', 'auth' => 'admin'],
                                    'refresh' => ['method' => 'POST', 'path' => '/api/admin/refresh', 'auth' => 'admin'],
                                ],
                                'dashboard' => [
                                    'stats' => ['method' => 'GET', 'path' => '/api/admin/dashboard/stats', 'auth' => 'viewer+'],
                                    'activity' => ['method' => 'GET', 'path' => '/api/admin/dashboard/activity', 'auth' => 'viewer+'],
                                ],
                                'posts' => [
                                    'list' => ['method' => 'GET', 'path' => '/api/admin/posts', 'auth' => 'editor+'],
                                    'show' => ['method' => 'GET', 'path' => '/api/admin/posts/{id}', 'auth' => 'editor+'],
                                    'create' => ['method' => 'POST', 'path' => '/api/admin/posts', 'auth' => 'editor+'],
                                    'update' => ['method' => 'PUT', 'path' => '/api/admin/posts/{id}', 'auth' => 'editor+'],
                                    'delete' => ['method' => 'DELETE', 'path' => '/api/admin/posts/{id}', 'auth' => 'admin+'],
                                    'publish' => ['method' => 'POST', 'path' => '/api/admin/posts/{id}/publish', 'auth' => 'editor+'],
                                    'unpublish' => ['method' => 'POST', 'path' => '/api/admin/posts/{id}/unpublish', 'auth' => 'editor+'],
                                ],
                                'categories' => [
                                    'list' => ['method' => 'GET', 'path' => '/api/admin/categories', 'auth' => 'editor+'],
                                    'show' => ['method' => 'GET', 'path' => '/api/admin/categories/{id}', 'auth' => 'editor+'],
                                    'create' => ['method' => 'POST', 'path' => '/api/admin/categories', 'auth' => 'admin+'],
                                    'update' => ['method' => 'PUT', 'path' => '/api/admin/categories/{id}', 'auth' => 'admin+'],
                                    'delete' => ['method' => 'DELETE', 'path' => '/api/admin/categories/{id}', 'auth' => 'admin+'],
                                ],
                                'services' => [
                                    'list' => ['method' => 'GET', 'path' => '/api/admin/services', 'auth' => 'editor+'],
                                    'show' => ['method' => 'GET', 'path' => '/api/admin/services/{id}', 'auth' => 'editor+'],
                                    'create' => ['method' => 'POST', 'path' => '/api/admin/services', 'auth' => 'admin+'],
                                    'update' => ['method' => 'PUT', 'path' => '/api/admin/services/{id}', 'auth' => 'editor+'],
                                    'delete' => ['method' => 'DELETE', 'path' => '/api/admin/services/{id}', 'auth' => 'admin+'],
                                    'reorder' => ['method' => 'POST', 'path' => '/api/admin/services/reorder', 'auth' => 'editor+'],
                                ],
                                'users' => [
                                    'list' => ['method' => 'GET', 'path' => '/api/admin/users', 'auth' => 'admin+'],
                                    'show' => ['method' => 'GET', 'path' => '/api/admin/users/{id}', 'auth' => 'admin+'],
                                    'create' => ['method' => 'POST', 'path' => '/api/admin/users', 'auth' => 'admin+'],
                                    'update' => ['method' => 'PUT', 'path' => '/api/admin/users/{id}', 'auth' => 'admin+'],
                                    'delete' => ['method' => 'DELETE', 'path' => '/api/admin/users/{id}', 'auth' => 'super_admin'],
                                    'activate' => ['method' => 'POST', 'path' => '/api/admin/users/{id}/activate', 'auth' => 'admin+'],
                                    'deactivate' => ['method' => 'POST', 'path' => '/api/admin/users/{id}/deactivate', 'auth' => 'admin+'],
                                    'assignRole' => ['method' => 'POST', 'path' => '/api/admin/users/{id}/role', 'auth' => 'super_admin'],
                                ],
                                'settings' => [
                                    'get' => ['method' => 'GET', 'path' => '/api/admin/settings', 'auth' => 'admin+'],
                                    'update' => ['method' => 'PUT', 'path' => '/api/admin/settings', 'auth' => 'admin+'],
                                ],
                            ];
                        }

                        private function getAiEndpointsDocs(): array
                        {
                            return [
                                'tools' => [
                                    'list' => [
                                        'method' => 'GET',
                                        'path' => '/api/ai/tools',
                                        'description' => 'List available AI tools based on user role',
                                        'auth' => true,
                                    ],
                                    'execute' => [
                                        'method' => 'POST',
                                        'path' => '/api/ai/tools/{id}/execute',
                                        'description' => 'Execute an AI tool',
                                        'auth' => true,
                                        'body' => ['input' => 'string (required)', 'options' => 'object (optional)'],
                                        'note' => 'Deducts credits based on tool cost',
                                    ],
                                ],
                                'credits' => [
                                    'balance' => [
                                        'method' => 'GET',
                                        'path' => '/api/ai/credits',
                                        'description' => 'Get current credit balance',
                                        'auth' => true,
                                    ],
                                    'purchase' => [
                                        'method' => 'POST',
                                        'path' => '/api/ai/credits/purchase',
                                        'description' => 'Purchase additional credits',
                                        'auth' => true,
                                        'status' => 'Coming soon (501)',
                                    ],
                                ],
                                'usage' => [
                                    'history' => [
                                        'method' => 'GET',
                                        'path' => '/api/ai/usage',
                                        'description' => 'Get AI tool usage history',
                                        'auth' => true,
                                        'queryParams' => ['per_page' => 'integer (1-100)'],
                                    ],
                                ],
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
                                            'user' => ['id' => 1, 'name' => 'John Doe', 'email' => 'user@example.com', 'role' => 'user'],
                                            'token' => 'your-bearer-token',
                                            'expiresAt' => '2026-01-27T00:00:00.000000Z',
                                        ],
                                    ],
                                ],
                                'getServices' => [
                                    'request' => [
                                        'method' => 'GET',
                                        'url' => '/api/services',
                                        'headers' => ['Accept' => 'application/json'],
                                    ],
                                    'response' => [
                                        'success' => true,
                                        'data' => [
                                            ['id' => 'uuid', 'title' => 'Web Development', 'slug' => 'web-development', 'shortDescription' => '...'],
                                        ],
                                    ],
                                ],
                                'createPost' => [
                                    'request' => [
                                        'method' => 'POST',
                                        'url' => '/api/admin/posts',
                                        'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json', 'Authorization' => 'Bearer {admin-token}'],
                                        'body' => [
                                            'title' => 'My New Post',
                                            'content' => 'Post content here...',
                                            'categoryId' => 'category-uuid',
                                            'status' => 'draft',
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
                                    '1. Configure CORS' => 'Add your frontend domain to FRONTEND_URL in backend .env',
                                    '2. Authentication' => 'Call login endpoint to receive Bearer token',
                                    '3. Store Token' => 'Store token securely (httpOnly cookie recommended for web)',
                                    '4. Make Requests' => 'Include token in Authorization header for protected routes',
                                    '5. Handle Errors' => 'Check success field and handle error responses appropriately',
                                ],
                                'cors' => [
                                    'note' => 'CORS is configured to allow requests from FRONTEND_URL and ADMIN_FRONTEND_URL',
                                    'localDevelopment' => 'localhost:3000, localhost:5173 are allowed in local environment',
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
