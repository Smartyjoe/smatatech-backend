<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $featured = $request->input('featured');

        $query = Testimonial::query()->latest();

        if ($search) {
            $query->where('client_name', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($featured !== null) {
            $query->where('featured', filter_var($featured, FILTER_VALIDATE_BOOLEAN));
        }

        $testimonials = $query->paginate($perPage);

        return $this->paginatedResponse($testimonials);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'clientName' => 'required|string|max:255',
            'clientImage' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'projectType' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $dbData = [
            'client_name' => $validated['clientName'],
            'client_image' => $validated['clientImage'] ?? null,
            'company' => $validated['company'] ?? null,
            'role' => $validated['role'] ?? null,
            'text' => $validated['text'],
            'rating' => $validated['rating'],
            'project_type' => $validated['projectType'] ?? null,
            'featured' => $validated['featured'] ?? false,
            'status' => $validated['status'],
        ];

        $testimonial = Testimonial::create($dbData);

        return $this->successResponse($testimonial, 'Testimonial created successfully', 201);
    }

    public function show($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        return $this->successResponse($testimonial);
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::findOrFail($id);

        $validated = $request->validate([
            'clientName' => 'sometimes|required|string|max:255',
            'clientImage' => 'nullable|string',
            'company' => 'nullable|string|max:255',
            'role' => 'nullable|string|max:255',
            'text' => 'sometimes|required|string',
            'rating' => 'sometimes|required|integer|min:1|max:5',
            'projectType' => 'nullable|string',
            'featured' => 'nullable|boolean',
            'status' => 'sometimes|required|in:active,inactive',
        ]);

        $dbData = [];
        if (isset($validated['clientName'])) $dbData['client_name'] = $validated['clientName'];
        if (isset($validated['clientImage'])) $dbData['client_image'] = $validated['clientImage'];
        if (isset($validated['company'])) $dbData['company'] = $validated['company'];
        if (isset($validated['role'])) $dbData['role'] = $validated['role'];
        if (isset($validated['text'])) $dbData['text'] = $validated['text'];
        if (isset($validated['rating'])) $dbData['rating'] = $validated['rating'];
        if (isset($validated['projectType'])) $dbData['project_type'] = $validated['projectType'];
        if (isset($validated['featured'])) $dbData['featured'] = $validated['featured'];
        if (isset($validated['status'])) $dbData['status'] = $validated['status'];

        $testimonial->update($dbData);

        return $this->successResponse($testimonial, 'Testimonial updated successfully');
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::findOrFail($id);
        $testimonial->delete();

        return $this->successResponse(null, 'Testimonial deleted successfully');
    }
}
