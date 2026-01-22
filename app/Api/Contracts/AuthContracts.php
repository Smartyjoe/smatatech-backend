<?php

namespace App\Api\Contracts;

use App\Api\ErrorCode;

/**
 * Authentication API Endpoint Contracts
 */
class AuthContracts
{
    public static function register(): void
    {
        self::registerUserAuth();
        self::registerAdminAuth();
    }

    protected static function registerUserAuth(): void
    {
        // Register
        ApiRegistry::register(
            EndpointContract::post('/api/auth/register', 'User Registration')
                ->description('Register a new user account')
                ->group('auth')
                ->tags('auth', 'user')
                ->public()
                ->rateLimit('5 per minute')
                ->requestBody(Schema::object()
                    ->property('name', 'string', true, 'John Doe', 'Full name')
                    ->property('email', 'email', true, 'john@example.com', 'Email address')
                    ->property('password', 'string', true, null, 'Password (min 8 characters)')
                    ->property('password_confirmation', 'string', true, null, 'Password confirmation')
                    ->required('name', 'email', 'password', 'password_confirmation')
                )
                ->response(Schema::object()
                    ->property('user', 'object', true)
                    ->property('token', 'string', true, 'Bearer token')
                    ->property('expiresAt', 'datetime', true)
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::VALIDATION_UNIQUE_CONSTRAINT,
                    ErrorCode::RATE_LIMIT_EXCEEDED
                )
        );

        // Login
        ApiRegistry::register(
            EndpointContract::post('/api/auth/login', 'User Login')
                ->description('Authenticate user and get access token')
                ->group('auth')
                ->tags('auth', 'user')
                ->public()
                ->rateLimit('5 per minute')
                ->requestBody(Schema::object()
                    ->property('email', 'email', true, 'john@example.com', 'Email address')
                    ->property('password', 'string', true, null, 'Password')
                    ->required('email', 'password')
                )
                ->response(Schema::object()
                    ->property('user', 'object', true)
                    ->property('token', 'string', true, 'Bearer token')
                    ->property('expiresAt', 'datetime', true)
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::AUTH_INVALID_CREDENTIALS,
                    ErrorCode::AUTH_ACCOUNT_DISABLED,
                    ErrorCode::RATE_LIMIT_EXCEEDED
                )
        );

        // Logout
        ApiRegistry::register(
            EndpointContract::post('/api/auth/logout', 'User Logout')
                ->description('Logout and revoke access token')
                ->group('auth')
                ->tags('auth', 'user')
                ->auth('sanctum')
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Logged out successfully')
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );

        // Me
        ApiRegistry::register(
            EndpointContract::get('/api/auth/me', 'Get Current User')
                ->description('Get authenticated user profile')
                ->group('auth')
                ->tags('auth', 'user')
                ->auth('sanctum')
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('name', 'string', true)
                    ->property('email', 'string', true)
                    ->property('avatar', 'string', false)
                    ->property('emailVerifiedAt', 'datetime', false)
                    ->property('createdAt', 'datetime', true)
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );

        // Refresh
        ApiRegistry::register(
            EndpointContract::post('/api/auth/refresh', 'Refresh Token')
                ->description('Refresh authentication token')
                ->group('auth')
                ->tags('auth', 'user')
                ->auth('sanctum')
                ->response(Schema::object()
                    ->property('token', 'string', true)
                    ->property('expiresAt', 'datetime', true)
                )
                ->errors(ErrorCode::AUTH_REQUIRED, ErrorCode::AUTH_TOKEN_EXPIRED)
        );

        // Forgot Password
        ApiRegistry::register(
            EndpointContract::post('/api/auth/forgot-password', 'Forgot Password')
                ->description('Request a password reset link')
                ->group('auth')
                ->tags('auth', 'user', 'password')
                ->public()
                ->rateLimit('5 per minute')
                ->requestBody(Schema::object()
                    ->property('email', 'email', true, 'john@example.com')
                    ->required('email')
                )
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Password reset link sent.')
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::RATE_LIMIT_EXCEEDED
                )
        );

        // Reset Password
        ApiRegistry::register(
            EndpointContract::post('/api/auth/reset-password', 'Reset Password')
                ->description('Reset password using token')
                ->group('auth')
                ->tags('auth', 'user', 'password')
                ->public()
                ->requestBody(Schema::object()
                    ->property('token', 'string', true, null, 'Reset token from email')
                    ->property('email', 'email', true, 'john@example.com')
                    ->property('password', 'string', true, null, 'New password')
                    ->property('password_confirmation', 'string', true, null, 'Password confirmation')
                    ->required('token', 'email', 'password', 'password_confirmation')
                )
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Password reset successfully.')
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::AUTH_TOKEN_INVALID,
                    ErrorCode::AUTH_TOKEN_EXPIRED
                )
        );
    }

    protected static function registerAdminAuth(): void
    {
        // Admin Login
        ApiRegistry::register(
            EndpointContract::post('/api/admin/login', 'Admin Login')
                ->description('Authenticate admin and get access token')
                ->group('admin-auth')
                ->tags('auth', 'admin')
                ->public()
                ->rateLimit('5 per minute')
                ->requestBody(Schema::object()
                    ->property('email', 'email', true, 'admin@example.com')
                    ->property('password', 'string', true, null)
                    ->required('email', 'password')
                )
                ->response(Schema::object()
                    ->property('user', 'object', true)
                    ->property('token', 'string', true)
                    ->property('expiresAt', 'datetime', true)
                )
                ->errors(
                    ErrorCode::VALIDATION_ERROR,
                    ErrorCode::AUTH_INVALID_CREDENTIALS,
                    ErrorCode::AUTH_ACCOUNT_DISABLED,
                    ErrorCode::RATE_LIMIT_EXCEEDED
                )
        );

        // Admin Logout
        ApiRegistry::register(
            EndpointContract::post('/api/admin/logout', 'Admin Logout')
                ->description('Logout admin and revoke token')
                ->group('admin-auth')
                ->tags('auth', 'admin')
                ->adminAuth()
                ->response(Schema::object()
                    ->property('message', 'string', true, 'Logged out successfully')
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );

        // Admin Me
        ApiRegistry::register(
            EndpointContract::get('/api/admin/me', 'Get Current Admin')
                ->description('Get authenticated admin profile')
                ->group('admin-auth')
                ->tags('auth', 'admin')
                ->adminAuth()
                ->response(Schema::object()
                    ->property('id', 'uuid', true)
                    ->property('name', 'string', true)
                    ->property('email', 'string', true)
                    ->property('avatar', 'string', false)
                    ->property('roleTitle', 'string', false)
                    ->property('bio', 'string', false)
                    ->property('role', 'string', true)
                    ->property('permissions', 'array', true)
                    ->property('lastLoginAt', 'datetime', false)
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );

        // Admin Refresh
        ApiRegistry::register(
            EndpointContract::post('/api/admin/refresh', 'Admin Refresh Token')
                ->description('Refresh admin authentication token')
                ->group('admin-auth')
                ->tags('auth', 'admin')
                ->adminAuth()
                ->response(Schema::object()
                    ->property('token', 'string', true)
                    ->property('expiresAt', 'datetime', true)
                )
                ->errors(ErrorCode::AUTH_REQUIRED)
        );
    }
}
