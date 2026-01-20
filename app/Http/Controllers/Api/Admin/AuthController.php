<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Admin;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * Admin login.
     * POST /admin/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Update last login
        $admin->update(['last_login_at' => now()]);

        // Create token with role as ability
        $role = $admin->getRoleNames()->first() ?? 'viewer';
        $expiresAt = now()->addDays(1);
        $token = $admin->createToken('admin-token', [$role], $expiresAt)->plainTextToken;

        // Log activity
        ActivityLog::log(
            'admin_login',
            'Admin logged in',
            "{$admin->name} logged in to the admin panel",
            $admin
        );

        return $this->successResponse([
            'user' => $admin->toApiResponse(),
            'token' => $token,
            'expiresAt' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * Admin logout.
     * POST /admin/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $admin = $request->user();
        
        // Revoke current token
        $admin->currentAccessToken()->delete();

        // Log activity
        ActivityLog::log(
            'admin_logout',
            'Admin logged out',
            "{$admin->name} logged out from the admin panel",
            $admin
        );

        return $this->successResponse(null, 'Successfully logged out.');
    }

    /**
     * Get current admin user.
     * GET /admin/me
     */
    public function me(Request $request): JsonResponse
    {
        $admin = $request->user();

        return $this->successResponse($admin->toApiResponse());
    }

    /**
     * Refresh admin token.
     * POST /admin/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $admin = $request->user();
        
        // Revoke current token
        $admin->currentAccessToken()->delete();

        // Create new token
        $role = $admin->getRoleNames()->first() ?? 'viewer';
        $expiresAt = now()->addDays(1);
        $token = $admin->createToken('admin-token', [$role], $expiresAt)->plainTextToken;

        return $this->successResponse([
            'user' => $admin->toApiResponse(),
            'token' => $token,
            'expiresAt' => $expiresAt->toIso8601String(),
        ]);
    }
}
