<?php

namespace App\Http\Middleware;

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

        // Check if token belongs to an Admin
        if ($accessToken->tokenable_type !== Admin::class) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Admin access required.',
                'errors' => [],
            ], 403);
        }

        $admin = $accessToken->tokenable;

        if (!$admin) {
            return response()->json([
                'success' => false,
                'message' => 'Admin not found.',
                'errors' => [],
            ], 401);
        }

        // Set the authenticated admin on the request
        $request->setUserResolver(fn () => $admin);

        return $next($request);
    }
}
