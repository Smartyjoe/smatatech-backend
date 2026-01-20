<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends BaseApiController
{
    /**
     * List all brands.
     * GET /admin/brands
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $brands = $query->orderBy('order')->get();

        return $this->successResponse($brands->map(fn ($brand) => $brand->toApiResponse()));
    }

    /**
     * Get brand details.
     * GET /admin/brands/{id}
     */
    public function show(string $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);

        return $this->successResponse($brand->toApiResponse());
    }

    /**
     * Create brand.
     * POST /admin/brands
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'status' => 'sometimes|string|in:active,inactive',
            'order' => 'sometimes|integer',
        ]);

        $maxOrder = Brand::max('order') ?? 0;

        $brand = Brand::create([
            'name' => $validated['name'],
            'logo' => $validated['logo'] ?? null,
            'website' => $validated['website'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'order' => $validated['order'] ?? ($maxOrder + 1),
        ]);

        ActivityLog::log(
            'brand_created',
            'Brand created',
            "Brand '{$brand->name}' was created",
            $request->user(),
            $brand
        );

        return $this->createdResponse($brand->toApiResponse());
    }

    /**
     * Update brand.
     * PUT /admin/brands/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'logo' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'status' => 'sometimes|string|in:active,inactive',
            'order' => 'sometimes|integer',
        ]);

        $updateData = [];
        if (isset($validated['name'])) $updateData['name'] = $validated['name'];
        if (array_key_exists('logo', $validated)) $updateData['logo'] = $validated['logo'];
        if (array_key_exists('website', $validated)) $updateData['website'] = $validated['website'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];
        if (isset($validated['order'])) $updateData['order'] = $validated['order'];

        $brand->update($updateData);

        ActivityLog::log(
            'brand_updated',
            'Brand updated',
            "Brand '{$brand->name}' was updated",
            $request->user(),
            $brand
        );

        return $this->successResponse($brand->fresh()->toApiResponse());
    }

    /**
     * Delete brand.
     * DELETE /admin/brands/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $brandName = $brand->name;

        $brand->delete();

        ActivityLog::log(
            'brand_deleted',
            'Brand deleted',
            "Brand '{$brandName}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Brand deleted successfully.');
    }

    /**
     * Reorder brands.
     * POST /admin/brands/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderedIds' => 'required|array',
            'orderedIds.*' => 'uuid|exists:brands,id',
        ]);

        foreach ($validated['orderedIds'] as $index => $id) {
            Brand::where('id', $id)->update(['order' => $index + 1]);
        }

        ActivityLog::log(
            'brands_reordered',
            'Brands reordered',
            'Brand display order was updated',
            $request->user()
        );

        return $this->successResponse(null, 'Brands reordered successfully.');
    }
}
