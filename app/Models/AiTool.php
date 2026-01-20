<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class AiTool extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'credits_per_use',
        'is_active',
        'required_role',
        'config',
    ];

    protected function casts(): array
    {
        return [
            'credits_per_use' => 'integer',
            'is_active' => 'boolean',
            'config' => 'array',
        ];
    }

    /**
     * Scope for active tools.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get usage logs for this tool.
     */
    public function usageLogs()
    {
        return $this->hasMany(AiUsageLog::class);
    }

    /**
     * Transform tool data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'icon' => $this->icon,
            'creditsPerUse' => $this->credits_per_use,
            'isActive' => $this->is_active,
            'requiredRole' => $this->required_role,
        ];
    }
}
