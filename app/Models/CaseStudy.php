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
        'problem',
        'solution',
        'result',
        'status',
        'publish_date',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
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
     * Transform case study data for API response.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'clientName' => $this->client_name,
            'industry' => $this->industry,
            'featuredImage' => $this->featured_image,
            'problem' => $this->problem,
            'solution' => $this->solution,
            'result' => $this->result,
            'status' => $this->status,
            'publishDate' => $this->publish_date?->format('Y-m-d'),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
