<?php

namespace App\Http\Middleware;

use App\Api\ApiResponse;
use App\Api\ErrorCode;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return ApiResponse::unauthorized('Authentication required.');
        }

        // For Admin model with Spatie roles
        if (method_exists($user, 'hasAnyRole') && is_callable([$user, 'getRoleNames'])) {
            $userRole = $user->getRoleNames()->first();
            
            // Role hierarchy for admins
            $roleHierarchy = [
                'super_admin' => ['super_admin', 'admin', 'editor', 'viewer'],
                'admin' => ['admin', 'editor', 'viewer'],
                'editor' => ['editor', 'viewer'],
                'viewer' => ['viewer'],
            ];

            $allowedRoles = $roleHierarchy[$userRole] ?? [$userRole];
            
            foreach ($roles as $role) {
                if (in_array($role, $allowedRoles)) {
                    return $next($request);
                }
            }
        }

        // For User model with simple role field
        if (isset($user->role)) {
            // Role hierarchy for public users
            $roleHierarchy = [
                'premium' => ['premium', 'subscriber', 'user'],
                'subscriber' => ['subscriber', 'user'],
                'user' => ['user'],
            ];

            $allowedRoles = $roleHierarchy[$user->role] ?? [$user->role];
            
            foreach ($roles as $role) {
                if (in_array($role, $allowedRoles)) {
                    return $next($request);
                }
            }
        }

        return ApiResponse::error(
            ErrorCode::FORBIDDEN_ROLE_REQUIRED,
            'You do not have the required role to access this resource.',
            ['required_roles' => $roles],
            403
        );
    }
}
