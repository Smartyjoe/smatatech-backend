<?php

namespace App\Http\Controllers\Api;

use App\Models\ActivityLog;
use App\Models\Credit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseApiController
{
    /**
     * User registration.
     * POST /auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'status' => 'active',
            'credits' => 50, // Default credits for new users
        ]);

        // Create credit balance record
        Credit::create([
            'user_id' => $user->id,
            'available' => 50,
            'used' => 0,
            'total' => 50,
        ]);

        // Create token
        $expiresAt = now()->addDays(7);
        $token = $user->createToken('user-token', [$user->role], $expiresAt)->plainTextToken;

        // Log activity
        ActivityLog::log(
            'user_registered',
            'New user registered',
            "{$user->name} registered an account",
            $user,
            $user
        );

        return $this->createdResponse([
            'user' => $user->toApiResponse(),
            'token' => $token,
            'expiresAt' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * User login.
     * POST /auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Your account has been deactivated.'],
            ]);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Create token
        $expiresAt = now()->addDays(7);
        $token = $user->createToken('user-token', [$user->role], $expiresAt)->plainTextToken;

        // Log activity
        ActivityLog::log(
            'user_login',
            'User logged in',
            "{$user->name} logged in",
            $user
        );

        return $this->successResponse([
            'user' => $user->toApiResponse(),
            'token' => $token,
            'expiresAt' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * User logout.
     * POST /auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $user->currentAccessToken()->delete();

        return $this->successResponse(null, 'Successfully logged out.');
    }

    /**
     * Get current user.
     * GET /auth/me
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->successResponse($user->toApiResponse());
    }

    /**
     * Refresh user token.
     * POST /auth/refresh
     */
    public function refresh(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Revoke current token
        $user->currentAccessToken()->delete();

        // Create new token
        $expiresAt = now()->addDays(7);
        $token = $user->createToken('user-token', [$user->role], $expiresAt)->plainTextToken;

        return $this->successResponse([
            'user' => $user->toApiResponse(),
            'token' => $token,
            'expiresAt' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * Request password reset.
     * POST /auth/forgot-password
     */
    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return $this->successResponse(null, 'Password reset link sent to your email.');
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }

    /**
     * Reset password.
     * POST /auth/reset-password
     */
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return $this->successResponse(null, 'Password has been reset successfully.');
        }

        throw ValidationException::withMessages([
            'email' => [__($status)],
        ]);
    }
}
