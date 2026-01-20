<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends BaseApiController
{
    /**
     * List all users (paginated).
     * GET /admin/users
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::query();

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $allowedSorts = ['name', 'email', 'created_at', 'status', 'role'];
        
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy === 'createdAt' ? 'created_at' : $sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $users = $query->paginate($perPage);

        return $this->paginatedResponse($users->through(fn ($user) => $user->toApiResponse()));
    }

    /**
     * Get user details.
     * GET /admin/users/{id}
     */
    public function show(string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        return $this->successResponse($user->toApiResponse());
    }

    /**
     * Create new user.
     * POST /admin/users
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'role' => 'sometimes|string|in:user,subscriber,premium',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
            'status' => $validated['status'] ?? 'active',
            'credits' => 50,
        ]);

        ActivityLog::log(
            'user_created',
            'User created',
            "User {$user->name} was created by admin",
            $request->user(),
            $user
        );

        return $this->createdResponse($user->toApiResponse());
    }

    /**
     * Update user.
     * PUT /admin/users/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => "sometimes|string|email|max:255|unique:users,email,{$id}",
            'password' => ['sometimes', Rules\Password::defaults()],
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        ActivityLog::log(
            'user_updated',
            'User updated',
            "User {$user->name} was updated",
            $request->user(),
            $user
        );

        return $this->successResponse($user->fresh()->toApiResponse());
    }

    /**
     * Delete user.
     * DELETE /admin/users/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $userName = $user->name;

        $user->delete();

        ActivityLog::log(
            'user_deleted',
            'User deleted',
            "User {$userName} was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'User deleted successfully.');
    }

    /**
     * Activate user.
     * POST /admin/users/{id}/activate
     */
    public function activate(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'active']);

        ActivityLog::log(
            'user_activated',
            'User activated',
            "User {$user->name} was activated",
            $request->user(),
            $user
        );

        return $this->successResponse($user->fresh()->toApiResponse(), 'User activated successfully.');
    }

    /**
     * Deactivate user.
     * POST /admin/users/{id}/deactivate
     */
    public function deactivate(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'inactive']);

        // Revoke all tokens
        $user->tokens()->delete();

        ActivityLog::log(
            'user_deactivated',
            'User deactivated',
            "User {$user->name} was deactivated",
            $request->user(),
            $user
        );

        return $this->successResponse($user->fresh()->toApiResponse(), 'User deactivated successfully.');
    }

    /**
     * Assign role to user.
     * POST /admin/users/{id}/role
     */
    public function assignRole(Request $request, string $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'role' => 'required|string|in:user,subscriber,premium',
        ]);

        $user->update(['role' => $validated['role']]);

        ActivityLog::log(
            'user_role_changed',
            'User role changed',
            "User {$user->name} role changed to {$validated['role']}",
            $request->user(),
            $user
        );

        return $this->successResponse($user->fresh()->toApiResponse(), 'User role updated successfully.');
    }
}
