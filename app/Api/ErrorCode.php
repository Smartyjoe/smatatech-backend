<?php

namespace App\Api;

/**
 * Global API Error Codes
 * 
 * All error responses must use one of these predefined codes.
 * This ensures frontend can reliably handle errors programmatically.
 */
final class ErrorCode
{
    // Authentication Errors (AUTH_*)
    public const AUTH_REQUIRED = 'AUTH_REQUIRED';
    public const AUTH_INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';
    public const AUTH_TOKEN_EXPIRED = 'AUTH_TOKEN_EXPIRED';
    public const AUTH_TOKEN_INVALID = 'AUTH_TOKEN_INVALID';
    public const AUTH_ACCOUNT_DISABLED = 'AUTH_ACCOUNT_DISABLED';
    public const AUTH_EMAIL_NOT_VERIFIED = 'AUTH_EMAIL_NOT_VERIFIED';

    // Authorization Errors (FORBIDDEN_*)
    public const FORBIDDEN = 'FORBIDDEN';
    public const FORBIDDEN_ROLE_REQUIRED = 'FORBIDDEN_ROLE_REQUIRED';
    public const FORBIDDEN_PERMISSION_REQUIRED = 'FORBIDDEN_PERMISSION_REQUIRED';
    public const FORBIDDEN_OWNER_ONLY = 'FORBIDDEN_OWNER_ONLY';

    // Validation Errors (VALIDATION_*)
    public const VALIDATION_ERROR = 'VALIDATION_ERROR';
    public const VALIDATION_INVALID_FORMAT = 'VALIDATION_INVALID_FORMAT';
    public const VALIDATION_REQUIRED_FIELD = 'VALIDATION_REQUIRED_FIELD';
    public const VALIDATION_UNIQUE_CONSTRAINT = 'VALIDATION_UNIQUE_CONSTRAINT';

    // Resource Errors (RESOURCE_*)
    public const NOT_FOUND = 'NOT_FOUND';
    public const RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    public const RESOURCE_ALREADY_EXISTS = 'RESOURCE_ALREADY_EXISTS';
    public const RESOURCE_DELETED = 'RESOURCE_DELETED';
    public const RESOURCE_LOCKED = 'RESOURCE_LOCKED';

    // Request Errors (REQUEST_*)
    public const BAD_REQUEST = 'BAD_REQUEST';
    public const METHOD_NOT_ALLOWED = 'METHOD_NOT_ALLOWED';
    public const REQUEST_INVALID_JSON = 'REQUEST_INVALID_JSON';
    public const REQUEST_METHOD_NOT_ALLOWED = 'REQUEST_METHOD_NOT_ALLOWED';
    public const REQUEST_CONTENT_TYPE_INVALID = 'REQUEST_CONTENT_TYPE_INVALID';
    public const CONFLICT = 'CONFLICT';

    // Rate Limiting (RATE_*)
    public const RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';

    // Server Errors (SERVER_*)
    public const SERVER_ERROR = 'SERVER_ERROR';
    public const SERVER_UNAVAILABLE = 'SERVER_UNAVAILABLE';
    public const SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';
    public const SERVER_MAINTENANCE = 'SERVER_MAINTENANCE';

    // Business Logic Errors (BUSINESS_*)
    public const INSUFFICIENT_CREDITS = 'INSUFFICIENT_CREDITS';
    public const FEATURE_DISABLED = 'FEATURE_DISABLED';
    public const OPERATION_FAILED = 'OPERATION_FAILED';

    // File/Upload Errors (FILE_*)
    public const FILE_TOO_LARGE = 'FILE_TOO_LARGE';
    public const FILE_INVALID_TYPE = 'FILE_INVALID_TYPE';
    public const FILE_UPLOAD_FAILED = 'FILE_UPLOAD_FAILED';

    /**
     * Get all error codes with descriptions.
     */
    public static function all(): array
    {
        return [
            // Authentication
            self::AUTH_REQUIRED => [
                'httpStatus' => 401,
                'description' => 'Authentication is required to access this resource',
            ],
            self::AUTH_INVALID_CREDENTIALS => [
                'httpStatus' => 401,
                'description' => 'The provided credentials are invalid',
            ],
            self::AUTH_TOKEN_EXPIRED => [
                'httpStatus' => 401,
                'description' => 'The authentication token has expired',
            ],
            self::AUTH_TOKEN_INVALID => [
                'httpStatus' => 401,
                'description' => 'The authentication token is invalid',
            ],
            self::AUTH_ACCOUNT_DISABLED => [
                'httpStatus' => 403,
                'description' => 'This account has been disabled',
            ],
            self::AUTH_EMAIL_NOT_VERIFIED => [
                'httpStatus' => 403,
                'description' => 'Email verification is required',
            ],

            // Authorization
            self::FORBIDDEN => [
                'httpStatus' => 403,
                'description' => 'You do not have permission to perform this action',
            ],
            self::FORBIDDEN_ROLE_REQUIRED => [
                'httpStatus' => 403,
                'description' => 'A specific role is required to access this resource',
            ],
            self::FORBIDDEN_PERMISSION_REQUIRED => [
                'httpStatus' => 403,
                'description' => 'A specific permission is required to perform this action',
            ],
            self::FORBIDDEN_OWNER_ONLY => [
                'httpStatus' => 403,
                'description' => 'Only the resource owner can perform this action',
            ],

            // Validation
            self::VALIDATION_ERROR => [
                'httpStatus' => 422,
                'description' => 'The request data failed validation',
            ],
            self::VALIDATION_INVALID_FORMAT => [
                'httpStatus' => 422,
                'description' => 'The data format is invalid',
            ],
            self::VALIDATION_REQUIRED_FIELD => [
                'httpStatus' => 422,
                'description' => 'A required field is missing',
            ],
            self::VALIDATION_UNIQUE_CONSTRAINT => [
                'httpStatus' => 422,
                'description' => 'The value already exists and must be unique',
            ],

            // Resource
            self::NOT_FOUND => [
                'httpStatus' => 404,
                'description' => 'The requested resource was not found',
            ],
            self::RESOURCE_NOT_FOUND => [
                'httpStatus' => 404,
                'description' => 'The specified resource does not exist',
            ],
            self::RESOURCE_ALREADY_EXISTS => [
                'httpStatus' => 409,
                'description' => 'A resource with this identifier already exists',
            ],
            self::RESOURCE_DELETED => [
                'httpStatus' => 410,
                'description' => 'The resource has been deleted',
            ],
            self::RESOURCE_LOCKED => [
                'httpStatus' => 423,
                'description' => 'The resource is locked and cannot be modified',
            ],

            // Request
            self::BAD_REQUEST => [
                'httpStatus' => 400,
                'description' => 'The request is malformed or invalid',
            ],
            self::METHOD_NOT_ALLOWED => [
                'httpStatus' => 405,
                'description' => 'The HTTP method is not allowed for this endpoint',
            ],
            self::REQUEST_INVALID_JSON => [
                'httpStatus' => 400,
                'description' => 'The request body contains invalid JSON',
            ],
            self::REQUEST_METHOD_NOT_ALLOWED => [
                'httpStatus' => 405,
                'description' => 'The HTTP method is not allowed for this endpoint',
            ],
            self::REQUEST_CONTENT_TYPE_INVALID => [
                'httpStatus' => 415,
                'description' => 'The content type is not supported',
            ],
            self::CONFLICT => [
                'httpStatus' => 409,
                'description' => 'The request conflicts with the current state of the resource',
            ],

            // Rate Limiting
            self::RATE_LIMIT_EXCEEDED => [
                'httpStatus' => 429,
                'description' => 'Too many requests, please try again later',
            ],

            // Server
            self::SERVER_ERROR => [
                'httpStatus' => 500,
                'description' => 'An unexpected server error occurred',
            ],
            self::SERVER_UNAVAILABLE => [
                'httpStatus' => 503,
                'description' => 'The service is temporarily unavailable',
            ],
            self::SERVICE_UNAVAILABLE => [
                'httpStatus' => 503,
                'description' => 'The service is temporarily unavailable',
            ],
            self::SERVER_MAINTENANCE => [
                'httpStatus' => 503,
                'description' => 'The server is under maintenance',
            ],

            // Business Logic
            self::INSUFFICIENT_CREDITS => [
                'httpStatus' => 402,
                'description' => 'Insufficient credits to perform this action',
            ],
            self::FEATURE_DISABLED => [
                'httpStatus' => 403,
                'description' => 'This feature is currently disabled',
            ],
            self::OPERATION_FAILED => [
                'httpStatus' => 422,
                'description' => 'The operation could not be completed',
            ],

            // File
            self::FILE_TOO_LARGE => [
                'httpStatus' => 413,
                'description' => 'The uploaded file exceeds the maximum size limit',
            ],
            self::FILE_INVALID_TYPE => [
                'httpStatus' => 422,
                'description' => 'The file type is not allowed',
            ],
            self::FILE_UPLOAD_FAILED => [
                'httpStatus' => 500,
                'description' => 'The file upload failed',
            ],
        ];
    }

    /**
     * Get HTTP status for an error code.
     */
    public static function getHttpStatus(string $code): int
    {
        return self::all()[$code]['httpStatus'] ?? 500;
    }

    /**
     * Get description for an error code.
     */
    public static function getDescription(string $code): string
    {
        return self::all()[$code]['description'] ?? 'Unknown error';
    }
}
