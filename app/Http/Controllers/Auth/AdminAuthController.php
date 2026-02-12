<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    use ApiResponse;

    private function getAdminAvatarUrl(): string
    {
        return '/smart.JPG';
    }

    /**
     * Admin login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return $this->errorResponse('Invalid credentials', 401);
        }

        if (!$admin->is_active) {
            return $this->errorResponse('Your account has been deactivated', 403);
        }

        // Delete old tokens
        $admin->tokens()->delete();

        // Create new token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return $this->successResponse([
            'user' => [
                'id' => (string) $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'avatar' => $this->getAdminAvatarUrl(),
                'role' => $admin->role,
                'permissions' => $admin->permissions ?? [],
                'createdAt' => $admin->created_at->toISOString(),
                'lastLoginAt' => $admin->updated_at->toISOString(),
            ],
            'token' => $token,
            'expiresAt' => now()->addDay()->toISOString(),
            'token_type' => 'Bearer',
        ], 'Login successful');
    }

    /**
     * Admin logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse(null, 'Logged out successfully');
    }

    /**
     * Get current admin user
     */
    public function me(Request $request)
    {
        $admin = $request->user();

        return $this->successResponse([
            'id' => (string) $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => $this->getAdminAvatarUrl(),
            'role' => $admin->role,
            'permissions' => $admin->permissions ?? [],
            'createdAt' => $admin->created_at->toISOString(),
            'lastLoginAt' => $admin->updated_at->toISOString(),
            'is_active' => $admin->is_active,
        ]);
    }

    /**
     * Refresh admin token
     */
    public function refresh(Request $request)
    {
        $admin = $request->user();

        // Delete old token
        $request->user()->currentAccessToken()->delete();

        // Create new token
        $token = $admin->createToken('admin-token')->plainTextToken;

        return $this->successResponse([
            'token' => $token,
            'expiresAt' => now()->addDay()->toISOString(),
            'token_type' => 'Bearer',
        ], 'Token refreshed successfully');
    }
}
