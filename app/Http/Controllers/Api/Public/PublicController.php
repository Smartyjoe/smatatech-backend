<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Brand;
use App\Models\CaseStudy;
use App\Models\Category;
use App\Models\Contact;
use App\Models\Post;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicController extends BaseApiController
{
    /**
     * Get published services.
     * GET /services
     */
    public function services(): JsonResponse
    {
        $services = Cache::remember('public_services', 3600, function () {
            return Service::published()
                ->ordered()
                ->get()
                ->map(fn ($s) => $s->toApiResponse());
        });

        return $this->successResponse($services);
    }

    /**
     * Get service by slug.
     * GET /services/{slug}
     */
    public function service(string $slug): JsonResponse
    {
        $service = Cache::remember("public_service_{$slug}", 3600, function () use ($slug) {
            return Service::published()->where('slug', $slug)->firstOrFail();
        });

        return $this->successResponse($service->toDetailedApiResponse());
    }

    /**
     * Get published case studies.
     * GET /case-studies
     */
    public function caseStudies(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 50);
        
        $caseStudies = CaseStudy::published()
            ->orderBy('publish_date', 'desc')
            ->paginate($perPage);

        return $this->paginatedResponse($caseStudies->through(fn ($cs) => $cs->toApiResponse()));
    }

    /**
     * Get case study by slug.
     * GET /case-studies/{slug}
     */
    public function caseStudy(string $slug): JsonResponse
    {
        $caseStudy = Cache::remember("public_case_study_{$slug}", 3600, function () use ($slug) {
            return CaseStudy::published()->where('slug', $slug)->firstOrFail();
        });

        return $this->successResponse($caseStudy->toDetailedApiResponse());
    }

    /**
     * Get published testimonials.
     * GET /testimonials
     */
    public function testimonials(Request $request): JsonResponse
    {
        $query = Testimonial::published();

        if ($request->boolean('featured')) {
            $query->featured();
        }

        $testimonials = Cache::remember('public_testimonials_' . ($request->boolean('featured') ? 'featured' : 'all'), 3600, function () use ($query) {
            return $query->orderBy('created_at', 'desc')
                ->get()
                ->map(fn ($t) => $t->toApiResponse());
        });

        return $this->successResponse($testimonials);
    }

    /**
     * Get published blog posts.
     * GET /posts
     */
    public function posts(Request $request): JsonResponse
    {
        $query = Post::with(['category', 'author'])->published();

        // Filter by category
        if ($categorySlug = $request->get('category')) {
            $category = Category::where('slug', $categorySlug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            }
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $perPage = min($request->get('per_page', 15), 50);
        $posts = $query->orderBy('published_at', 'desc')->paginate($perPage);

        return $this->paginatedResponse($posts->through(fn ($p) => $p->toPublicApiResponse()));
    }

    /**
     * Get blog post by slug.
     * GET /posts/{slug}
     */
    public function post(string $slug): JsonResponse
    {
        $post = Post::with(['category', 'author'])
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return $this->successResponse($post->toDetailedApiResponse());
    }

    /**
     * Get blog categories.
     * GET /categories
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('public_categories', 3600, function () {
            return Category::where('status', 'active')
                ->withCount(['posts' => fn ($q) => $q->published()])
                ->orderBy('name')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'slug' => $c->slug,
                    'description' => $c->description,
                    'postsCount' => $c->posts_count,
                ]);
        });

        return $this->successResponse($categories);
    }

    /**
     * Get active brands.
     * GET /brands
     */
    public function brands(): JsonResponse
    {
        $brands = Cache::remember('public_brands', 3600, function () {
            return Brand::active()
                ->ordered()
                ->get()
                ->map(fn ($b) => $b->toApiResponse());
        });

        return $this->successResponse($brands);
    }

    /**
     * Get public site settings.
     * GET /settings
     */
    public function settings(): JsonResponse
    {
        $settings = Cache::remember('public_site_settings', 3600, function () {
            return SiteSetting::getPublicSettings();
        });

        return $this->successResponse($settings);
    }

    /**
     * Submit contact form.
     * POST /contact
     */
    public function submitContact(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'company' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'projectType' => 'nullable|string|max:100',
            'budget' => 'nullable',
            'services' => 'nullable|array',
            'services.*' => 'string',
            'message' => 'required|string|max:5000',
            'metadata' => 'nullable|array',
            'metadata.source' => 'nullable|string|max:500',
            'metadata.referrer' => 'nullable|string|max:500',
            'metadata.utmSource' => 'nullable|string|max:255',
            'metadata.utmMedium' => 'nullable|string|max:255',
            'metadata.utmCampaign' => 'nullable|string|max:255',
        ]);

        $metadata = $validated['metadata'] ?? [];

        $contact = Contact::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'company' => $validated['company'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'project_type' => $validated['projectType'] ?? null,
            'budget' => $validated['budget'] ?? null,
            'services' => $validated['services'] ?? null,
            'message' => $validated['message'],
            'status' => 'unread',
            'source_url' => $metadata['source'] ?? null,
            'referrer' => $metadata['referrer'] ?? null,
            'utm_source' => $metadata['utmSource'] ?? null,
            'utm_medium' => $metadata['utmMedium'] ?? null,
            'utm_campaign' => $metadata['utmCampaign'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        // Log activity (non-blocking)
        ActivityLog::log(
            'contact_received',
            'New contact message',
            "Contact form submitted by {$contact->name}",
            null,
            $contact
        );

        return $this->createdResponse(null, 'Thank you for your message. We will get back to you soon.');
    }

    /**
     * Get related posts by category/tags.
     * GET /posts/{slug}/related
     */
    public function relatedPosts(string $slug): JsonResponse
    {
        $post = Post::with(['category'])->published()->where('slug', $slug)->firstOrFail();

        $relatedPosts = Post::with(['category', 'author'])
            ->published()
            ->where('id', '!=', $post->id)
            ->where(function ($query) use ($post) {
                if ($post->category_id) {
                    $query->where('category_id', $post->category_id);
                }
                if ($post->tags && is_array($post->tags)) {
                    $query->orWhereJsonContains('tags', $post->tags);
                }
            })
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return $this->successResponse($relatedPosts->map(fn ($p) => [
            'id' => $p->id,
            'title' => $p->title,
            'slug' => $p->slug,
            'excerpt' => $p->excerpt,
            'featuredImage' => $p->featured_image ? url($p->featured_image) : null,
            'publishedAt' => $p->published_at?->toIso8601String(),
        ]));
    }

    /**
     * Get related case studies by industry.
     * GET /case-studies/{slug}/related
     */
    public function relatedCaseStudies(string $slug): JsonResponse
    {
        $caseStudy = CaseStudy::published()->where('slug', $slug)->firstOrFail();

        $relatedCaseStudies = CaseStudy::published()
            ->where('id', '!=', $caseStudy->id)
            ->where('industry', $caseStudy->industry)
            ->orderBy('publish_date', 'desc')
            ->limit(3)
            ->get();

        return $this->successResponse($relatedCaseStudies->map(fn ($cs) => $cs->toApiResponse()));
    }
}
