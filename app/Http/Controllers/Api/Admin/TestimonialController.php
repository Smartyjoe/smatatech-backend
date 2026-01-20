<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TestimonialController extends BaseApiController
{
    /**
     * List all testimonials.
     * GET /admin/testimonials
     */
    public function index(Request $request): JsonResponse
    {
        $query = Testimonial::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        $perPage = min($request->get('per_page', 15), 100);
        $testimonials = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return $this->paginatedResponse($testimonials->through(fn ($t) => $t->toApiResponse()));
    }

    /**
     * Get testimonial details.
     * GET /admin/testimonials/{id}
     */
    public function show(string $id): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);

        return $this->successResponse($testimonial->toApiResponse());
    }

    /**
     * Create testimonial.
     * POST /admin/testimonials
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'clientName' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:100',
            'testimonialText' => 'required|string',
            'avatar' => 'nullable|string',
            'isFeatured' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:draft,published',
        ]);

        $testimonial = Testimonial::create([
            'client_name' => $validated['clientName'],
            'company' => $validated['company'] ?? null,
            'role' => $validated['role'] ?? null,
            'testimonial_text' => $validated['testimonialText'],
            'avatar' => $validated['avatar'] ?? null,
            'is_featured' => $validated['isFeatured'] ?? false,
            'status' => $validated['status'] ?? 'draft',
        ]);

        ActivityLog::log(
            'testimonial_created',
            'Testimonial created',
            "Testimonial from '{$testimonial->client_name}' was created",
            $request->user(),
            $testimonial
        );

        return $this->createdResponse($testimonial->toApiResponse());
    }

    /**
     * Update testimonial.
     * PUT /admin/testimonials/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'clientName' => 'sometimes|string|max:255',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:100',
            'testimonialText' => 'sometimes|string',
            'avatar' => 'nullable|string',
            'isFeatured' => 'sometimes|boolean',
            'status' => 'sometimes|string|in:draft,published',
        ]);

        $updateData = [];
        if (isset($validated['clientName'])) $updateData['client_name'] = $validated['clientName'];
        if (array_key_exists('company', $validated)) $updateData['company'] = $validated['company'];
        if (array_key_exists('role', $validated)) $updateData['role'] = $validated['role'];
        if (isset($validated['testimonialText'])) $updateData['testimonial_text'] = $validated['testimonialText'];
        if (array_key_exists('avatar', $validated)) $updateData['avatar'] = $validated['avatar'];
        if (isset($validated['isFeatured'])) $updateData['is_featured'] = $validated['isFeatured'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];

        $testimonial->update($updateData);

        ActivityLog::log(
            'testimonial_updated',
            'Testimonial updated',
            "Testimonial from '{$testimonial->client_name}' was updated",
            $request->user(),
            $testimonial
        );

        return $this->successResponse($testimonial->fresh()->toApiResponse());
    }

    /**
     * Delete testimonial.
     * DELETE /admin/testimonials/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $testimonial = Testimonial::findOrFail($id);
        $clientName = $testimonial->client_name;

        $testimonial->delete();

        ActivityLog::log(
            'testimonial_deleted',
            'Testimonial deleted',
            "Testimonial from '{$clientName}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Testimonial deleted successfully.');
    }
}
