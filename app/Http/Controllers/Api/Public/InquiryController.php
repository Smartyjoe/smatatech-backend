<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\Service;
use App\Models\ServiceInquiry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class InquiryController extends BaseApiController
{
    /**
     * Submit a service inquiry.
     * POST /inquiries
     */
    public function store(Request $request): JsonResponse
    {
        // Rate limiting
        $key = 'inquiry:' . ($request->ip() ?? 'unknown');
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->errorResponse(
                "Too many requests. Please wait {$seconds} seconds.",
                429
            );
        }
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'serviceSlug' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'budgetRange' => 'nullable|string|max:50',
            'timeline' => 'nullable|string|max:50',
            'message' => 'required|string|max:5000',
        ]);

        // Find service
        $service = Service::where('slug', $validated['serviceSlug'])->first();

        $inquiry = ServiceInquiry::create([
            'service_id' => $service?->id,
            'service_slug' => $validated['serviceSlug'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company' => $validated['company'] ?? null,
            'budget_range' => $validated['budgetRange'] ?? null,
            'timeline' => $validated['timeline'] ?? null,
            'message' => $validated['message'],
            'status' => 'new',
            'ip_address' => $request->ip(),
        ]);

        // Log activity
        ActivityLog::log(
            'inquiry_received',
            'New service inquiry',
            "Service inquiry received from {$inquiry->name} for {$validated['serviceSlug']}",
            null,
            $inquiry
        );

        return $this->createdResponse(null, 'Your inquiry has been submitted successfully. We will get back to you soon!');
    }
}
