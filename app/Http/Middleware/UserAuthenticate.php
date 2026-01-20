<?php

namespace App\Http\Middleware;

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
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'errors' => [],
            ], 401);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid token.',
                'errors' => [],
            ], 401);
        }

        // Check if token belongs to a User
        if ($accessToken->tokenable_type !== User::class) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. User access required.',
                'errors' => [],
            ], 403);
        }

        $user = $accessToken->tokenable;

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.',
                'errors' => [],
            ], 401);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
                'errors' => [],
            ], 403);
        }

        // Set the authenticated user on the request
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
