<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\BlogCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BlogPostController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $categoryId = $request->input('category_id');

        $query = BlogPost::with('category')->latest();

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $posts = $query->paginate($perPage);

        return $this->paginatedResponse($posts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:blog_posts,slug',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'featuredImage' => 'nullable|string',
            'categoryId' => 'nullable|exists:blog_categories,id',
            'author' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'status' => 'required|in:published,draft',
            'publishedAt' => 'nullable|date',
            'metaTitle' => 'nullable|string',
            'metaDescription' => 'nullable|string',
            'metaKeywords' => 'nullable|string',
        ]);

        $dbData = [
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? null,
            'excerpt' => $validated['excerpt'] ?? null,
            'content' => $validated['content'],
            'featured_image' => $validated['featuredImage'] ?? null,
            'category_id' => $validated['categoryId'] ?? null,
            'author' => $validated['author'] ?? null,
            'tags' => $validated['tags'] ?? null,
            'status' => $validated['status'],
            'published_at' => $validated['publishedAt'] ?? null,
            'meta_title' => $validated['metaTitle'] ?? null,
            'meta_description' => $validated['metaDescription'] ?? null,
            'meta_keywords' => $validated['metaKeywords'] ?? null,
        ];

        // Set published_at if status is published and not set
        if ($dbData['status'] === 'published' && empty($dbData['published_at'])) {
            $dbData['published_at'] = now();
        }

        $post = BlogPost::create(array_filter($dbData, fn($value) => $value !== null));

        // Update category post count
        if ($post->category_id) {
            $category = BlogCategory::find($post->category_id);
            if ($category) {
                $category->increment('post_count');
            }
        }

        return $this->successResponse($post->load('category'), 'Blog post created successfully', 201);
    }

    public function show($id)
    {
        $post = BlogPost::with('category', 'comments')->findOrFail($id);
        return $this->successResponse($post);
    }

    public function update(Request $request, $id)
    {
        $post = BlogPost::findOrFail($id);
        $oldCategoryId = $post->category_id;

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|unique:blog_posts,slug,' . $id,
            'excerpt' => 'nullable|string',
            'content' => 'sometimes|required|string',
            'featuredImage' => 'nullable|string',
            'categoryId' => 'nullable|exists:blog_categories,id',
            'author' => 'nullable|string|max:255',
            'tags' => 'nullable|array',
            'status' => 'sometimes|required|in:published,draft',
            'publishedAt' => 'nullable|date',
            'metaTitle' => 'nullable|string',
            'metaDescription' => 'nullable|string',
            'metaKeywords' => 'nullable|string',
        ]);

        $dbData = [];
        if (isset($validated['title'])) $dbData['title'] = $validated['title'];
        if (isset($validated['slug'])) $dbData['slug'] = $validated['slug'];
        if (isset($validated['excerpt'])) $dbData['excerpt'] = $validated['excerpt'];
        if (isset($validated['content'])) $dbData['content'] = $validated['content'];
        if (isset($validated['featuredImage'])) $dbData['featured_image'] = $validated['featuredImage'];
        if (isset($validated['categoryId'])) $dbData['category_id'] = $validated['categoryId'];
        if (isset($validated['author'])) $dbData['author'] = $validated['author'];
        if (isset($validated['tags'])) $dbData['tags'] = $validated['tags'];
        if (isset($validated['status'])) $dbData['status'] = $validated['status'];
        if (isset($validated['publishedAt'])) $dbData['published_at'] = $validated['publishedAt'];
        if (isset($validated['metaTitle'])) $dbData['meta_title'] = $validated['metaTitle'];
        if (isset($validated['metaDescription'])) $dbData['meta_description'] = $validated['metaDescription'];
        if (isset($validated['metaKeywords'])) $dbData['meta_keywords'] = $validated['metaKeywords'];

        // Set published_at if status changed to published
        if (isset($dbData['status']) && $dbData['status'] === 'published' && !$post->published_at) {
            $dbData['published_at'] = now();
        }

        $post->update($dbData);

        // Update category post counts if category changed
        if (isset($dbData['category_id']) && $oldCategoryId !== $dbData['category_id']) {
            if ($oldCategoryId) {
                BlogCategory::find($oldCategoryId)?->decrement('post_count');
            }
            if ($dbData['category_id']) {
                BlogCategory::find($dbData['category_id'])?->increment('post_count');
            }
        }

        return $this->successResponse($post->load('category'), 'Blog post updated successfully');
    }

    public function destroy($id)
    {
        $post = BlogPost::findOrFail($id);
        $categoryId = $post->category_id;

        $post->delete();

        // Update category post count
        if ($categoryId) {
            BlogCategory::find($categoryId)?->decrement('post_count');
        }

        return $this->successResponse(null, 'Blog post deleted successfully');
    }
}
