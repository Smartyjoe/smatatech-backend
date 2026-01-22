<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API Version Configuration
    |--------------------------------------------------------------------------
    |
    | Configure API versioning settings. The default version is used when
    | no version is specified in the request.
    |
    */

    'versioning' => [
        'enabled' => env('API_VERSIONING_ENABLED', true),
        'default' => env('API_DEFAULT_VERSION', 'v1'),
        'supported' => ['v1'], // Add 'v2' when ready
        'header' => 'X-API-Version', // Alternative version header
        'deprecation_warning' => true, // Add deprecation header for old versions
    ],

    /*
    |--------------------------------------------------------------------------
    | Request Validation
    |--------------------------------------------------------------------------
    |
    | Enable automatic request validation against API contracts.
    | When enabled, incoming requests are validated against the schema
    | defined in the endpoint contract.
    |
    */

    'request_validation' => [
        'enabled' => env('API_REQUEST_VALIDATION', true),
        'strict_mode' => env('API_STRICT_MODE', false), // Reject unknown fields
        'log_failures' => env('API_LOG_VALIDATION_FAILURES', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Response Validation
    |--------------------------------------------------------------------------
    |
    | Enable response validation in development/testing environments.
    | This helps catch API contract violations before they reach production.
    |
    */

    'response_validation' => [
        'enabled' => env('API_RESPONSE_VALIDATION', false),
        'environments' => ['local', 'testing', 'staging'], // Only validate in these
        'log_violations' => true,
        'throw_exceptions' => env('API_RESPONSE_VALIDATION_STRICT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Swagger UI Configuration
    |--------------------------------------------------------------------------
    |
    | Configure the Swagger UI documentation interface.
    |
    */

    'swagger' => [
        'enabled' => env('API_SWAGGER_ENABLED', true),
        'path' => '/api/swagger',
        'title' => env('APP_NAME', 'API') . ' Documentation',
        'persist_authorization' => true,
        'deep_linking' => true,
        'display_request_duration' => true,
        'doc_expansion' => 'list', // 'list', 'full', 'none'
        'filter' => true,
        'show_extensions' => true,
        'show_common_extensions' => true,
        'try_it_out_enabled' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | API Documentation
    |--------------------------------------------------------------------------
    |
    | General documentation settings.
    |
    */

    'documentation' => [
        'cache_ttl' => env('API_DOCS_CACHE_TTL', 3600), // 1 hour
        'contact_email' => env('API_CONTACT_EMAIL', 'api@example.com'),
        'terms_of_service' => env('API_TERMS_URL'),
        'license' => [
            'name' => 'Proprietary',
            'url' => null,
        ],
    ],

];
