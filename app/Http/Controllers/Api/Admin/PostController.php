<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends BaseApiController
{
    /**
     * List all posts (paginated).
     * GET /admin/posts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Post::with(['category', 'author']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Filter by category
        if ($categoryId = $request->get('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $perPage = min($request->get('per_page', 15), 100);
        $posts = $query->paginate($perPage);

        return $this->paginatedResponse($posts->through(fn ($post) => $post->toApiResponse()));
    }

    /**
     * Get post details.
     * GET /admin/posts/{id}
     */
    public function show(string $id): JsonResponse
    {
        $post = Post::with(['category', 'author'])->findOrFail($id);

        return $this->successResponse($post->toApiResponse());
    }

    /**
     * Create new post.
     * POST /admin/posts
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featuredImage' => 'nullable|string',
            'categoryId' => 'nullable|uuid|exists:categories,id',
            'status' => 'sometimes|string|in:draft,published',
            'metaTitle' => 'nullable|string|max:255',
            'metaDescription' => 'nullable|string',
            'publishedAt' => 'nullable|date',
        ]);

        $post = Post::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $validated['featuredImage'] ?? null,
            'category_id' => $validated['categoryId'] ?? null,
            'author_id' => $request->user()->id,
            'status' => $validated['status'] ?? 'draft',
            'meta_title' => $validated['metaTitle'] ?? null,
            'meta_description' => $validated['metaDescription'] ?? null,
            'published_at' => $validated['publishedAt'] ?? null,
        ]);

        ActivityLog::log(
            'post_created',
            'Blog post created',
            "Post '{$post->title}' was created",
            $request->user(),
            $post
        );

        return $this->createdResponse($post->load(['category', 'author'])->toApiResponse());
    }

    /**
     * Update post.
     * PUT /admin/posts/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $post = Post::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|string',
            'featuredImage' => 'nullable|string',
            'categoryId' => 'nullable|uuid|exists:categories,id',
            'status' => 'sometimes|string|in:draft,published',
            'metaTitle' => 'nullable|string|max:255',
            'metaDescription' => 'nullable|string',
            'publishedAt' => 'nullable|date',
        ]);

        $updateData = [];
        
        if (isset($validated['title'])) {
            $updateData['title'] = $validated['title'];
            $updateData['slug'] = Str::slug($validated['title']);
        }
        if (array_key_exists('excerpt', $validated)) $updateData['excerpt'] = $validated['excerpt'];
        if (isset($validated['content'])) $updateData['content'] = $validated['content'];
        if (array_key_exists('featuredImage', $validated)) $updateData['featured_image'] = $validated['featuredImage'];
        if (array_key_exists('categoryId', $validated)) $updateData['category_id'] = $validated['categoryId'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];
        if (array_key_exists('metaTitle', $validated)) $updateData['meta_title'] = $validated['metaTitle'];
        if (array_key_exists('metaDescription', $validated)) $updateData['meta_description'] = $validated['metaDescription'];
        if (array_key_exists('publishedAt', $validated)) $updateData['published_at'] = $validated['publishedAt'];

        $post->update($updateData);

        ActivityLog::log(
            'post_updated',
            'Blog post updated',
            "Post '{$post->title}' was updated",
            $request->user(),
            $post
        );

        return $this->successResponse($post->fresh()->load(['category', 'author'])->toApiResponse());
    }

    /**
     * Delete post.
     * DELETE /admin/posts/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        $postTitle = $post->title;

        $post->delete();

        ActivityLog::log(
            'post_deleted',
            'Blog post deleted',
            "Post '{$postTitle}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Post deleted successfully.');
    }

    /**
     * Publish post.
     * POST /admin/posts/{id}/publish
     */
    public function publish(Request $request, string $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        
        $post->update([
            'status' => 'published',
            'published_at' => $post->published_at ?? now(),
        ]);

        ActivityLog::log(
            'post_published',
            'Blog post published',
            "Post '{$post->title}' was published",
            $request->user(),
            $post
        );

        return $this->successResponse($post->fresh()->load(['category', 'author'])->toApiResponse(), 'Post published successfully.');
    }

    /**
     * Unpublish post.
     * POST /admin/posts/{id}/unpublish
     */
    public function unpublish(Request $request, string $id): JsonResponse
    {
        $post = Post::findOrFail($id);
        
        $post->update(['status' => 'draft']);

        ActivityLog::log(
            'post_unpublished',
            'Blog post unpublished',
            "Post '{$post->title}' was unpublished",
            $request->user(),
            $post
        );

        return $this->successResponse($post->fresh()->load(['category', 'author'])->toApiResponse(), 'Post unpublished successfully.');
    }
}
