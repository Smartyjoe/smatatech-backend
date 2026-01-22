<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'category_id',
        'author_id',
        'status',
        'meta_title',
        'meta_description',
        'og_image',
        'read_time',
        'is_featured',
        'tags',
        'comments_enabled',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'comments_enabled' => 'boolean',
            'tags' => 'array',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });
    }

    /**
     * Get the category of this post.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the author of this post.
     */
    public function author()
    {
        return $this->belongsTo(Admin::class, 'author_id');
    }

    /**
     * Get the comments for this post.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Scope for published posts.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Transform post data for API response (admin).
     * Always returns consistent structure - never null for nested objects.
     */
    public function toApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featuredImage' => $this->getAbsoluteUrl($this->featured_image),
            'categoryId' => $this->category_id,
            'category' => $this->category ? $this->category->toApiResponse() : [
                'id' => null,
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
                'description' => null,
                'status' => 'active',
            ],
            'author' => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'avatar' => $this->getAbsoluteUrl($this->author->avatar),
                'role' => $this->author->role_title ?? null,
                'bio' => $this->author->bio ?? null,
            ] : [
                'id' => null,
                'name' => 'Unknown Author',
                'avatar' => null,
                'role' => null,
                'bio' => null,
            ],
            'readTime' => $this->read_time ?? $this->calculateReadTime(),
            'isFeatured' => (bool) $this->is_featured,
            'tags' => $this->tags ?? [],
            'commentsEnabled' => (bool) ($this->comments_enabled ?? true),
            'status' => $this->status,
            'metaTitle' => $this->meta_title,
            'metaDescription' => $this->meta_description,
            'publishedAt' => $this->published_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Transform post data for public API response (list view).
     * Always returns consistent structure - never null for nested objects.
     */
    public function toPublicApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'featuredImage' => $this->getAbsoluteUrl($this->featured_image),
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : [
                'id' => null,
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
            ],
            'author' => $this->author ? [
                'name' => $this->author->name,
                'avatar' => $this->getAbsoluteUrl($this->author->avatar),
            ] : [
                'name' => 'Unknown Author',
                'avatar' => null,
            ],
            'readTime' => $this->read_time ?? $this->calculateReadTime(),
            'isFeatured' => (bool) $this->is_featured,
            'publishedAt' => $this->published_at?->toIso8601String(),
        ];
    }

    /**
     * Transform post data for detailed public API response.
     */
    public function toDetailedApiResponse(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featuredImage' => $this->getAbsoluteUrl($this->featured_image),
            'category' => $this->category ? [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ] : [
                'id' => null,
                'name' => 'Uncategorized',
                'slug' => 'uncategorized',
            ],
            'author' => $this->author ? [
                'name' => $this->author->name,
                'role' => $this->author->role_title ?? null,
                'avatar' => $this->getAbsoluteUrl($this->author->avatar),
                'bio' => $this->author->bio ?? null,
            ] : [
                'name' => 'Unknown Author',
                'role' => null,
                'avatar' => null,
                'bio' => null,
            ],
            'tags' => $this->tags ?? [],
            'readTime' => $this->read_time ?? $this->calculateReadTime(),
            'seo' => [
                'metaTitle' => $this->meta_title,
                'metaDescription' => $this->meta_description,
                'ogImage' => $this->getAbsoluteUrl($this->og_image ?? $this->featured_image),
            ],
            'commentsEnabled' => (bool) ($this->comments_enabled ?? true),
            'publishedAt' => $this->published_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }

    /**
     * Calculate estimated read time based on content.
     */
    protected function calculateReadTime(): string
    {
        $wordCount = str_word_count(strip_tags($this->content ?? ''));
        $minutes = max(1, ceil($wordCount / 200));
        return $minutes . ' min read';
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
