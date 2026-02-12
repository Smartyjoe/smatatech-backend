<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of brands
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Brand::query()->orderBy('order', 'asc');

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        $brands = $query->paginate($perPage);

        return $this->paginatedResponse($brands);
    }

    /**
     * Store a newly created brand
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'website' => 'nullable|url',
            'status' => 'required|in:active,inactive',
            'order' => 'nullable|integer',
        ]);

        $brand = Brand::create($validated);

        return $this->successResponse($brand, 'Brand created successfully', 201);
    }

    /**
     * Display the specified brand
     */
    public function show($id)
    {
        $brand = Brand::findOrFail($id);

        return $this->successResponse($brand);
    }

    /**
     * Update the specified brand
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'logo' => 'nullable|string',
            'website' => 'nullable|url',
            'status' => 'sometimes|required|in:active,inactive',
            'order' => 'nullable|integer',
        ]);

        $brand->update($validated);

        return $this->successResponse($brand, 'Brand updated successfully');
    }

    /**
     * Remove the specified brand
     */
    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return $this->successResponse(null, 'Brand deleted successfully');
    }
}
