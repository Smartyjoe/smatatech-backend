<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of services
     */
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Service::query()->orderBy('order', 'asc');

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        $services = $query->paginate($perPage);

        return $this->paginatedResponse($services);
    }

    /**
     * Store a newly created service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:services,slug',
            'shortDescription' => 'nullable|string',
            'fullDescription' => 'nullable|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|string',
            'features' => 'nullable|array',
            'benefits' => 'nullable|array',
            'processSteps' => 'nullable|array',
            'order' => 'nullable|integer',
            'status' => 'required|in:draft,published',
            'metaTitle' => 'nullable|string',
            'metaDescription' => 'nullable|string',
        ]);

        // Map camelCase to snake_case for database
        $dbData = [
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? null,
            'short_description' => $validated['shortDescription'] ?? null,
            'long_description' => $validated['fullDescription'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'image' => $validated['image'] ?? null,
            'features' => $validated['features'] ?? null,
            'benefits' => $validated['benefits'] ?? null,
            'process' => $validated['processSteps'] ?? null,
            'order' => $validated['order'] ?? 0,
            'status' => $validated['status'] === 'published' ? 'active' : 'inactive',
            'meta_title' => $validated['metaTitle'] ?? null,
            'meta_description' => $validated['metaDescription'] ?? null,
        ];

        $service = Service::create(array_filter($dbData, fn($value) => $value !== null));

        return $this->successResponse($service, 'Service created successfully', 201);
    }

    /**
     * Display the specified service
     */
    public function show($id)
    {
        $service = Service::findOrFail($id);

        return $this->successResponse($service);
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|unique:services,slug,' . $id,
            'shortDescription' => 'nullable|string',
            'fullDescription' => 'nullable|string',
            'icon' => 'nullable|string',
            'image' => 'nullable|string',
            'features' => 'nullable|array',
            'benefits' => 'nullable|array',
            'processSteps' => 'nullable|array',
            'order' => 'nullable|integer',
            'status' => 'sometimes|required|in:draft,published',
            'metaTitle' => 'nullable|string',
            'metaDescription' => 'nullable|string',
        ]);

        // Map camelCase to snake_case for database
        $dbData = [];
        
        if (isset($validated['title'])) $dbData['title'] = $validated['title'];
        if (isset($validated['slug'])) $dbData['slug'] = $validated['slug'];
        if (isset($validated['shortDescription'])) $dbData['short_description'] = $validated['shortDescription'];
        if (isset($validated['fullDescription'])) $dbData['long_description'] = $validated['fullDescription'];
        if (isset($validated['icon'])) $dbData['icon'] = $validated['icon'];
        if (isset($validated['image'])) $dbData['image'] = $validated['image'];
        if (isset($validated['features'])) $dbData['features'] = $validated['features'];
        if (isset($validated['benefits'])) $dbData['benefits'] = $validated['benefits'];
        if (isset($validated['processSteps'])) $dbData['process'] = $validated['processSteps'];
        if (isset($validated['order'])) $dbData['order'] = $validated['order'];
        if (isset($validated['status'])) $dbData['status'] = $validated['status'] === 'published' ? 'active' : 'inactive';
        if (isset($validated['metaTitle'])) $dbData['meta_title'] = $validated['metaTitle'];
        if (isset($validated['metaDescription'])) $dbData['meta_description'] = $validated['metaDescription'];

        $service->update($dbData);

        return $this->successResponse($service, 'Service updated successfully');
    }

    /**
     * Remove the specified service
     */
    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return $this->successResponse(null, 'Service deleted successfully');
    }
}
