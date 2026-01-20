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
        'company',
        'role',
        'testimonial_text',
        'avatar',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
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
            'company' => $this->company,
            'role' => $this->role,
            'testimonialText' => $this->testimonial_text,
            'avatar' => $this->avatar,
            'isFeatured' => $this->is_featured,
            'status' => $this->status,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
