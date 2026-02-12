<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\CaseStudy;
use App\Models\ContactMessage;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use ApiResponse;

    /**
     * Get dashboard statistics
     */
    public function stats(Request $request)
    {
        $stats = [
            'users' => [
                'total' => User::count(),
                'recent' => User::where('created_at', '>=', now()->subDays(30))->count(),
            ],
            'posts' => [
                'total' => BlogPost::count(),
                'published' => BlogPost::where('status', 'published')->count(),
                'draft' => BlogPost::where('status', 'draft')->count(),
            ],
            'services' => [
                'total' => Service::count(),
                'active' => Service::where('status', 'active')->count(),
            ],
            'case_studies' => [
                'total' => CaseStudy::count(),
                'published' => CaseStudy::where('status', 'published')->count(),
            ],
            'testimonials' => [
                'total' => Testimonial::count(),
                'featured' => Testimonial::where('featured', true)->count(),
            ],
            'brands' => [
                'total' => Brand::count(),
                'active' => Brand::where('status', 'active')->count(),
            ],
            'contacts' => [
                'total' => ContactMessage::count(),
                'unread' => ContactMessage::where('read', false)->count(),
            ],
        ];

        return $this->successResponse($stats);
    }

    /**
     * Get recent activity
     */
    public function activity(Request $request)
    {
        $activities = [];

        // Recent users
        $recentUsers = User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']);
        foreach ($recentUsers as $user) {
            $activities[] = [
                'type' => 'user',
                'message' => "New user registered: {$user->name}",
                'timestamp' => $user->created_at,
            ];
        }

        // Recent blog posts
        $recentPosts = BlogPost::latest()->take(5)->get(['id', 'title', 'created_at']);
        foreach ($recentPosts as $post) {
            $activities[] = [
                'type' => 'post',
                'message' => "New blog post: {$post->title}",
                'timestamp' => $post->created_at,
            ];
        }

        // Recent contact messages
        $recentContacts = ContactMessage::latest()->take(5)->get(['id', 'name', 'created_at']);
        foreach ($recentContacts as $contact) {
            $activities[] = [
                'type' => 'contact',
                'message' => "New contact message from: {$contact->name}",
                'timestamp' => $contact->created_at,
            ];
        }

        // Sort by timestamp
        usort($activities, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        // Take top 20
        $activities = array_slice($activities, 0, 20);

        return $this->successResponse($activities);
    }
}
