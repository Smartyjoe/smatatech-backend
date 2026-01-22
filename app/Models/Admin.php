<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Admin extends Authenticatable
{
    use HasApiTokens, HasRoles, Notifiable, SoftDeletes;

    protected $guard_name = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'role_title',
        'bio',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the posts authored by this admin.
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    /**
     * Get all permissions for API response.
     */
    public function getPermissionsArray(): array
    {
        $role = $this->getRoleNames()->first();
        
        if ($role === 'super_admin') {
            return ['*'];
        }

        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Transform admin data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->getAbsoluteUrl($this->avatar),
            'roleTitle' => $this->role_title,
            'bio' => $this->bio,
            'role' => $this->getRoleNames()->first() ?? 'viewer',
            'permissions' => $this->getPermissionsArray(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'lastLoginAt' => $this->last_login_at?->toIso8601String(),
        ];
    }

    /**
     * Get absolute URL for media files.
     */
    protected function getAbsoluteUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        return url($path);
    }
}
