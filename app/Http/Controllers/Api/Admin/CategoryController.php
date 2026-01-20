<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends BaseApiController
{
    /**
     * List all categories.
     * GET /admin/categories
     */
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('posts');

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $categories = $query->orderBy('name')->get();

        return $this->successResponse($categories->map(fn ($cat) => $cat->toApiResponse()));
    }

    /**
     * Get category details.
     * GET /admin/categories/{id}
     */
    public function show(string $id): JsonResponse
    {
        $category = Category::withCount('posts')->findOrFail($id);

        return $this->successResponse($category->toApiResponse());
    }

    /**
     * Create category.
     * POST /admin/categories
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        $category = Category::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);

        ActivityLog::log(
            'category_created',
            'Category created',
            "Category '{$category->name}' was created",
            $request->user(),
            $category
        );

        return $this->createdResponse($category->toApiResponse());
    }

    /**
     * Update category.
     * PUT /admin/categories/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'status' => 'sometimes|string|in:active,inactive',
        ]);

        $updateData = [];
        if (isset($validated['name'])) {
            $updateData['name'] = $validated['name'];
            $updateData['slug'] = Str::slug($validated['name']);
        }
        if (array_key_exists('description', $validated)) $updateData['description'] = $validated['description'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];

        $category->update($updateData);

        ActivityLog::log(
            'category_updated',
            'Category updated',
            "Category '{$category->name}' was updated",
            $request->user(),
            $category
        );

        return $this->successResponse($category->fresh()->toApiResponse());
    }

    /**
     * Delete category.
     * DELETE /admin/categories/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $categoryName = $category->name;

        $category->delete();

        ActivityLog::log(
            'category_deleted',
            'Category deleted',
            "Category '{$categoryName}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Category deleted successfully.');
    }
}
