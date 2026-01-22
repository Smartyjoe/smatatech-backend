<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class CaseStudy extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'case_studies';

    protected $fillable = [
        'title',
        'slug',
        'client_name',
        'industry',
        'featured_image',
        'short_description',
        'duration',
        'year',
        'problem',
        'solution',
        'result',
        'challenge_overview',
        'challenge_points',
        'solution_overview',
        'solution_points',
        'results_data',
        'process_steps',
        'technologies',
        'testimonial_quote',
        'testimonial_author',
        'testimonial_role',
        'gallery',
        'meta_title',
        'meta_description',
        'highlight_stat_value',
        'highlight_stat_label',
        'status',
        'publish_date',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'challenge_points' => 'array',
            'solution_points' => 'array',
            'results_data' => 'array',
            'process_steps' => 'array',
            'technologies' => 'array',
            'gallery' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($caseStudy) {
            if (empty($caseStudy->slug)) {
                $caseStudy->slug = Str::slug($caseStudy->title);
            }
        });
    }

    /**
     * Scope for published case studies.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Transform case study data for API response (list view).
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'clientName' => $this->client_name,
            'industry' => $this->industry,
            'featuredImage' => $this->getAbsoluteUrl($this->featured_image),
            'shortDescription' => $this->short_description ?? $this->problem,
            'highlightStat' => $this->highlight_stat_value ? [
                'value' => $this->highlight_stat_value,
                'label' => $this->highlight_stat_label,
            ] : null,
            'status' => $this->status,
            'publishDate' => $this->publish_date?->format('Y-m-d'),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }

    /**
     * Transform case study data for detailed API response.
     */
    public function toDetailedApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'clientName' => $this->client_name,
            'industry' => $this->industry,
            'duration' => $this->duration,
            'year' => $this->year,
            'featuredImage' => $this->getAbsoluteUrl($this->featured_image),
            'shortDescription' => $this->short_description,
            'challenge' => [
                'overview' => $this->challenge_overview ?? $this->problem,
                'points' => $this->challenge_points ?? [],
            ],
            'solution' => [
                'overview' => $this->solution_overview ?? $this->solution,
                'points' => $this->solution_points ?? [],
            ],
            'results' => $this->results_data ?? [],
            'processSteps' => $this->process_steps ?? [],
            'technologies' => $this->technologies ?? [],
            'testimonial' => $this->testimonial_quote ? [
                'quote' => $this->testimonial_quote,
                'author' => $this->testimonial_author,
                'role' => $this->testimonial_role,
            ] : null,
            'gallery' => $this->formatGallery(),
            'seo' => [
                'metaTitle' => $this->meta_title,
                'metaDescription' => $this->meta_description,
                'ogImage' => $this->getAbsoluteUrl($this->featured_image),
            ],
            'status' => $this->status,
            'publishDate' => $this->publish_date?->format('Y-m-d'),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Format gallery with absolute URLs.
     */
    protected function formatGallery(): array
    {
        if (!$this->gallery) {
            return [];
        }
        return array_map(function ($item) {
            return [
                'type' => $item['type'] ?? 'image',
                'url' => $this->getAbsoluteUrl($item['url'] ?? null),
                'caption' => $item['caption'] ?? null,
            ];
        }, $this->gallery);
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
