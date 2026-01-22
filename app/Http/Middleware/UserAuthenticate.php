<?php

namespace App\Http\Middleware;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

class UserAuthenticate
{
    /**
     * Handle an incoming request.
     * Ensures the authenticated user is a regular User.
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

        // Check if token belongs to a User
        if ($accessToken->tokenable_type !== User::class) {
            return ApiResponse::error(
                ErrorCode::FORBIDDEN,
                'User access required.',
                null,
                403
            );
        }

        $user = $accessToken->tokenable;

        if (!$user) {
            return ApiResponse::error(
                ErrorCode::AUTH_TOKEN_INVALID,
                'User account not found.',
                null,
                401
            );
        }

        // Check if user is active
        if (isset($user->status) && $user->status !== 'active') {
            return ApiResponse::error(
                ErrorCode::AUTH_ACCOUNT_DISABLED,
                'Your account has been deactivated.',
                null,
                403
            );
        }

        // Set the authenticated user on the request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
