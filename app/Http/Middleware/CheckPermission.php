<?php

namespace App\Http\Middleware;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$permissions
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Authentication required.');
        }

        // Super admin has all permissions
        if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
            return $next($request);
        }

        // Check if user has any of the required permissions
        if (method_exists($user, 'hasAnyPermission')) {
            if ($user->hasAnyPermission($permissions)) {
                return $next($request);
            }
        }

        return ApiResponse::error(
            ErrorCode::FORBIDDEN_PERMISSION_REQUIRED,
            'You do not have the required permission to perform this action.',
            ['required_permissions' => $permissions],
            403
        );
    }
}
