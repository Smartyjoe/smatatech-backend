<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CaseStudy extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'client',
        'industry',
        'challenge',
        'solution',
        'results',
        'testimonial',
        'technologies',
        'image',
        'gallery',
        'status',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'technologies' => 'array',
        'gallery' => 'array',
        'created_at' => 'datetime:c',
        'updated_at' => 'datetime:c',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($caseStudy) {
            if (empty($caseStudy->slug)) {
                $caseStudy->slug = Str::slug($caseStudy->title);
            }
        });

        static::updating(function ($caseStudy) {
            if ($caseStudy->isDirty('title') && empty($caseStudy->slug)) {
                $caseStudy->slug = Str::slug($caseStudy->title);
            }
        });
    }
}
