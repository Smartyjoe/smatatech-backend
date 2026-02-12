<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BlogCategoryController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');

        $query = BlogCategory::query()->latest();

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $categories = $query->paginate($perPage);

        return $this->paginatedResponse($categories);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:blog_categories,slug',
            'description' => 'nullable|string',
        ]);

        $category = BlogCategory::create($validated);

        return $this->successResponse($category, 'Category created successfully', 201);
    }

    public function show($id)
    {
        $category = BlogCategory::findOrFail($id);
        return $this->successResponse($category);
    }

    public function update(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|unique:blog_categories,slug,' . $id,
            'description' => 'nullable|string',
        ]);

        $category->update($validated);

        return $this->successResponse($category, 'Category updated successfully');
    }

    public function destroy($id)
    {
        $category = BlogCategory::findOrFail($id);
        
        // Check if category has posts
        if ($category->post_count > 0) {
            return $this->errorResponse('Cannot delete category with existing posts', 400);
        }

        $category->delete();

        return $this->successResponse(null, 'Category deleted successfully');
    }
}
