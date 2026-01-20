<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\CaseStudy;
use App\Models\ChatbotConfig;
use App\Models\Contact;
use App\Models\Post;
use App\Models\Service;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends BaseApiController
{
    /**
     * Get dashboard statistics.
     * GET /admin/dashboard/stats
     */
    public function stats(): JsonResponse
    {
        $chatbotConfig = ChatbotConfig::current();

        $stats = [
            'totalPosts' => Post::count(),
            'totalUsers' => User::count(),
            'totalContacts' => Contact::count(),
            'totalServices' => Service::count(),
            'totalCaseStudies' => CaseStudy::count(),
            'totalTestimonials' => Testimonial::count(),
            'chatbotStatus' => $chatbotConfig->is_enabled ? 'active' : 'inactive',
            'recentActivity' => $this->getRecentActivity(5),
        ];

        return $this->successResponse($stats);
    }

    /**
     * Get recent activity.
     * GET /admin/dashboard/activity
     */
    public function activity(Request $request): JsonResponse
    {
        $limit = min($request->get('limit', 20), 100);
        
        $activities = $this->getRecentActivity($limit);

        return $this->successResponse($activities);
    }

    /**
     * Get recent activity logs.
     */
    private function getRecentActivity(int $limit): array
    {
        return ActivityLog::with('actor')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn ($log) => $log->toApiResponse())
            ->toArray();
    }
}
