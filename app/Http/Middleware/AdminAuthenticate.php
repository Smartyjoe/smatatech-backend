<?php

namespace App\Http\Middleware;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user is an Admin.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return ApiResponse::unauthorized('Authentication required.');
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return ApiResponse::error(
                ErrorCode::AUTH_TOKEN_INVALID,
                'Invalid or expired token.',
                null,
                401
            );
        }

        // Check if token belongs to an Admin
        if ($accessToken->tokenable_type !== Admin::class) {
            return ApiResponse::error(
                ErrorCode::FORBIDDEN,
                'Admin access required.',
                null,
                403
            );
        }

        $admin = $accessToken->tokenable;

        if (!$admin) {
            return ApiResponse::error(
                ErrorCode::AUTH_TOKEN_INVALID,
                'Admin account not found.',
                null,
                401
            );
        }

        // Check if admin is active
        if (isset($admin->status) && $admin->status !== 'active') {
            return ApiResponse::error(
                ErrorCode::AUTH_ACCOUNT_DISABLED,
                'Your admin account has been disabled.',
                null,
                403
            );
        }

        // Set the authenticated admin on the request
        $request->setUserResolver(fn () => $admin);

        return $next($request);
    }
}
