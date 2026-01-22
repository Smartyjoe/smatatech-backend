<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'short_description',
        'full_description',
        'icon',
        'image',
        'features',
        'benefits',
        'process_steps',
        'meta_title',
        'meta_description',
        'og_image',
        'status',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'features' => 'array',
            'benefits' => 'array',
            'process_steps' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($service) {
            if (empty($service->slug)) {
                $service->slug = Str::slug($service->title);
            }
        });
    }

    /**
     * Scope for published services.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for ordered services.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    /**
     * Transform service data for API response (list view).
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'shortDescription' => $this->short_description,
            'fullDescription' => $this->full_description,
            'icon' => $this->icon,
            'image' => $this->image ? $this->getAbsoluteUrl($this->image) : null,
            'features' => $this->features ?? [],
            'status' => $this->status,
            'order' => $this->order,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Transform service data for detailed API response.
     */
    public function toDetailedApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'shortDescription' => $this->short_description,
            'fullDescription' => $this->full_description,
            'icon' => $this->icon,
            'image' => $this->image ? $this->getAbsoluteUrl($this->image) : null,
            'features' => $this->features ?? [],
            'benefits' => $this->benefits ?? [],
            'processSteps' => $this->process_steps ?? [],
            'seo' => [
                'metaTitle' => $this->meta_title,
                'metaDescription' => $this->meta_description,
                'ogImage' => $this->og_image ? $this->getAbsoluteUrl($this->og_image) : null,
            ],
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
