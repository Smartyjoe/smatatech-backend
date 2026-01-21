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

        return $this->successResponse($service->toApiResponse());
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

        return $this->successResponse($caseStudy->toApiResponse());
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

        return $this->successResponse($post->toPublicApiResponse());
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
            'budget' => 'nullable|numeric|min:0',
            'services' => 'nullable|array',
            'services.*' => 'string',
            'message' => 'required|string|max:5000',
        ]);

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
        ]);

        // Log activity (non-blocking - don't fail if logging fails)
        try {
            ActivityLog::log(
                'contact_received',
                'New contact message',
                "Contact form submitted by {$contact->name}",
                null,
                $contact
            );
        } catch (\Exception $e) {
            // Silently fail - contact was still created successfully
            \Log::warning('Failed to log contact activity: ' . $e->getMessage());
        }

        return $this->createdResponse(null, 'Thank you for your message. We will get back to you soon.');
    }
}
