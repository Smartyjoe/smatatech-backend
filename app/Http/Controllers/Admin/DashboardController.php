<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Models\BlogPost;
use App\Models\Brand;
use App\Models\CaseStudy;
use App\Models\ContactMessage;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
        $limit = (int) $request->input('limit', 20);
        $limit = max(1, min($limit, 50));
        $activities = [];

        // Recent users
        $recentUsers = User::latest()->take(10)->get(['id', 'name', 'email', 'created_at']);
        foreach ($recentUsers as $user) {
            $activities[] = [
                'id' => "user-{$user->id}",
                'type' => 'user',
                'message' => "New user registered: {$user->name}",
                'timestamp' => $user->created_at,
            ];
        }

        // Recent blog posts
        $recentPosts = BlogPost::latest()->take(10)->get(['id', 'title', 'created_at']);
        foreach ($recentPosts as $post) {
            $activities[] = [
                'id' => "post-{$post->id}",
                'type' => 'post',
                'message' => "New blog post: {$post->title}",
                'timestamp' => $post->created_at,
            ];
        }

        // Recent contact + consultation messages
        $recentContacts = ContactMessage::latest()
            ->take(15)
            ->get(['id', 'name', 'project_type', 'budget', 'services', 'created_at']);
        foreach ($recentContacts as $contact) {
            $projectType = strtolower((string) ($contact->project_type ?? ''));
            $isConsultation = Str::contains($projectType, 'consult')
                || (empty($contact->budget) && empty($contact->services) && !empty($projectType));

            $activityType = $isConsultation ? 'consultation' : 'contact';
            $label = $isConsultation ? 'consultation request' : 'contact message';
            $activities[] = [
                'id' => "contact-{$contact->id}",
                'type' => $activityType,
                'message' => "New {$label} from: {$contact->name}",
                'timestamp' => $contact->created_at,
            ];
        }

        // Recent blog comments
        $recentComments = BlogComment::query()
            ->latest()
            ->take(15)
            ->with(['post:id,title'])
            ->get(['id', 'post_id', 'author_name', 'status', 'created_at']);

        foreach ($recentComments as $comment) {
            $title = $comment->post?->title ?: 'a blog post';
            $activities[] = [
                'id' => "comment-{$comment->id}",
                'type' => 'comment',
                'message' => "New comment by {$comment->author_name} on {$title}",
                'timestamp' => $comment->created_at,
            ];
        }

        // Sort by timestamp
        usort($activities, function ($a, $b) {
            return $b['timestamp'] <=> $a['timestamp'];
        });

        $activities = array_slice($activities, 0, $limit);

        return $this->successResponse($activities);
    }
}
