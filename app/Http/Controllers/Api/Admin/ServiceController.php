<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceController extends BaseApiController
{
    /**
     * List all services.
     * GET /admin/services
     */
    public function index(Request $request): JsonResponse
    {
        $query = Service::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $services = $query->orderBy('order')->get();

        return $this->successResponse($services->map(fn ($service) => $service->toApiResponse()));
    }

    /**
     * Get service details.
     * GET /admin/services/{id}
     */
    public function show(string $id): JsonResponse
    {
        $service = Service::findOrFail($id);

        return $this->successResponse($service->toApiResponse());
    }

    /**
     * Create service.
     * POST /admin/services
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'shortDescription' => 'nullable|string',
            'fullDescription' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'status' => 'sometimes|string|in:draft,published',
            'order' => 'sometimes|integer',
        ]);

        $maxOrder = Service::max('order') ?? 0;

        $service = Service::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'short_description' => $validated['shortDescription'] ?? null,
            'full_description' => $validated['fullDescription'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'image' => $validated['image'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'order' => $validated['order'] ?? ($maxOrder + 1),
        ]);

        ActivityLog::log(
            'service_created',
            'Service created',
            "Service '{$service->title}' was created",
            $request->user(),
            $service
        );

        return $this->createdResponse($service->toApiResponse());
    }

    /**
     * Update service.
     * PUT /admin/services/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'shortDescription' => 'nullable|string',
            'fullDescription' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'image' => 'nullable|string',
            'status' => 'sometimes|string|in:draft,published',
            'order' => 'sometimes|integer',
        ]);

        $updateData = [];
        if (isset($validated['title'])) {
            $updateData['title'] = $validated['title'];
            $updateData['slug'] = Str::slug($validated['title']);
        }
        if (array_key_exists('shortDescription', $validated)) $updateData['short_description'] = $validated['shortDescription'];
        if (array_key_exists('fullDescription', $validated)) $updateData['full_description'] = $validated['fullDescription'];
        if (array_key_exists('icon', $validated)) $updateData['icon'] = $validated['icon'];
        if (array_key_exists('image', $validated)) $updateData['image'] = $validated['image'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];
        if (isset($validated['order'])) $updateData['order'] = $validated['order'];

        $service->update($updateData);

        ActivityLog::log(
            'service_updated',
            'Service updated',
            "Service '{$service->title}' was updated",
            $request->user(),
            $service
        );

        return $this->successResponse($service->fresh()->toApiResponse());
    }

    /**
     * Delete service.
     * DELETE /admin/services/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $serviceTitle = $service->title;

        $service->delete();

        ActivityLog::log(
            'service_deleted',
            'Service deleted',
            "Service '{$serviceTitle}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Service deleted successfully.');
    }

    /**
     * Reorder services.
     * POST /admin/services/reorder
     */
    public function reorder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'orderedIds' => 'required|array',
            'orderedIds.*' => 'uuid|exists:services,id',
        ]);

        foreach ($validated['orderedIds'] as $index => $id) {
            Service::where('id', $id)->update(['order' => $index + 1]);
        }

        ActivityLog::log(
            'services_reordered',
            'Services reordered',
            'Service display order was updated',
            $request->user()
        );

        return $this->successResponse(null, 'Services reordered successfully.');
    }
}
