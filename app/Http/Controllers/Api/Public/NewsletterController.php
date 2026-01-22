<?php

namespace App\Http\Controllers\Api\Public;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class NewsletterController extends BaseApiController
{
    /**
     * Subscribe to newsletter.
     * POST /newsletter/subscribe
     */
    public function subscribe(Request $request): JsonResponse
    {
        // Rate limiting
        $key = 'newsletter:' . ($request->ip() ?? 'unknown');
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return $this->errorResponse(
                "Too many requests. Please wait {$seconds} seconds.",
                429
            );
        }
        RateLimiter::hit($key, 60);

        $validated = $request->validate([
            'email' => 'required|email|max:255',
            'consent' => 'required|boolean|accepted',
        ]);

        // Check if already subscribed
        $existing = NewsletterSubscriber::where('email', $validated['email'])->first();
        
        if ($existing) {
            if ($existing->status === 'active') {
                return $this->successResponse(null, 'You are already subscribed to our newsletter.');
            }
            
            // Re-subscribe
            $existing->update([
                'status' => 'active',
                'consent' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);
            
            return $this->successResponse(null, 'Welcome back! You have been re-subscribed to our newsletter.');
        }

        // Create new subscriber
        NewsletterSubscriber::create([
            'email' => $validated['email'],
            'consent' => true,
            'status' => 'active',
            'ip_address' => $request->ip(),
            'subscribed_at' => now(),
        ]);

        return $this->createdResponse(null, 'Successfully subscribed to our newsletter!');
    }

    /**
     * Unsubscribe from newsletter.
     * POST /newsletter/unsubscribe
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => 'required|email|max:255',
        ]);

        $subscriber = NewsletterSubscriber::where('email', $validated['email'])->first();
        
        if (!$subscriber) {
            return $this->errorResponse('Email not found in our newsletter list.', 404);
        }

        $subscriber->update([
            'status' => 'unsubscribed',
            'unsubscribed_at' => now(),
        ]);

        return $this->successResponse(null, 'You have been unsubscribed from our newsletter.');
    }
}
