<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AdminAuthController extends Controller
{
    use ApiResponse;

    private function supportsAvatarColumn(): bool
    {
        return Schema::hasColumn('admins', 'avatar');
    }

    private function getAdminAvatarUrl(Admin $admin): string
    {
        if (!$this->supportsAvatarColumn()) {
            return '/smart.JPG';
        }
        $avatar = $admin->avatar;
        if (!$avatar) {
            return '/smart.JPG';
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        if (str_starts_with($avatar, '/storage/')) {
            return $avatar;
        }

        if (str_starts_with($avatar, 'storage/')) {
            return '/' . $avatar;
        }

        if (str_starts_with($avatar, '/')) {
            return $avatar;
        }

        return Storage::url($avatar);
    }

    private function normalizeAvatarForStorage(?string $avatar): ?string
    {
        if ($avatar === null || $avatar === '') {
            return null;
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            $path = parse_url($avatar, PHP_URL_PATH);
            if (is_string($path) && str_starts_with($path, '/storage/')) {
                return ltrim(substr($path, strlen('/storage/')), '/');
            }
            return $avatar;
        }

        if (str_starts_with($avatar, '/storage/')) {
            return ltrim(substr($avatar, strlen('/storage/')), '/');
        }

        if (str_starts_with($avatar, 'storage/')) {
            return ltrim(substr($avatar, strlen('storage/')), '/');
        }

        return ltrim($avatar, '/');
    }

    private function formatAdmin(Admin $admin): array
    {
        return [
            'id' => (string) $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'avatar' => $this->getAdminAvatarUrl($admin),
            'role' => $admin->role,
            'permissions' => $admin->permissions ?? [],
            'createdAt' => $admin->created_at->toISOString(),
            'lastLoginAt' => $admin->updated_at->toISOString(),
            'is_active' => $admin->is_active,
        ];
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
            'user' => $this->formatAdmin($admin),
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

        return $this->successResponse($this->formatAdmin($admin));
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

    /**
     * Update admin profile details
     */
    public function updateProfile(Request $request)
    {
        $admin = $request->user();
        $hasAvatarColumn = $this->supportsAvatarColumn();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:admins,email,' . $admin->id,
            'avatar' => $hasAvatarColumn ? 'nullable|string|max:2048' : 'nullable',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($hasAvatarColumn && array_key_exists('avatar', $validated)) {
            $updateData['avatar'] = $this->normalizeAvatarForStorage($validated['avatar']);
        }

        $admin->update($updateData);

        return $this->successResponse($this->formatAdmin($admin->fresh()), 'Profile updated successfully');
    }

    /**
     * Update admin password
     */
    public function updatePassword(Request $request)
    {
        $admin = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $admin->update([
            'password' => $validated['password'],
        ]);

        return $this->successResponse(null, 'Password updated successfully');
    }
}
