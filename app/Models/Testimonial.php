<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'client_name',
        'client_title',
        'client_company',
        'company',
        'role',
        'content',
        'testimonial_text',
        'avatar',
        'rating',
        'project_type',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'rating' => 'integer',
        ];
    }

    /**
     * Scope for published testimonials.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for featured testimonials.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Transform testimonial data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'clientName' => $this->client_name,
            'company' => $this->client_company ?? $this->company,
            'role' => $this->client_title ?? $this->role,
            'testimonialText' => $this->content ?? $this->testimonial_text,
            'avatar' => $this->getAbsoluteUrl($this->avatar),
            'rating' => $this->rating ?? 5,
            'projectType' => $this->project_type,
            'isFeatured' => (bool) $this->is_featured,
            'status' => $this->status,
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
