<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Brand extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'logo',
        'website',
        'website_url',
        'status',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    /**
     * Scope for active brands.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for ordered brands.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Transform brand data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'logo' => $this->getAbsoluteUrl($this->logo),
            'website' => $this->website,
            'websiteUrl' => $this->website_url ?? $this->website,
            'status' => $this->status,
            'order' => $this->order,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
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
