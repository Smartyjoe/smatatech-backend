<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiContentService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AiContentController extends Controller
{
    use ApiResponse;

    public function generateService(Request $request, AiContentService $service)
    {
        set_time_limit(180);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'audience' => 'nullable|string|max:1000',
            'goals' => 'nullable|string|max:1000',
            'tone' => 'nullable|string|max:100',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:80',
        ]);

        try {
            $result = $service->generateService($validated);
            return $this->successResponse($result, 'Service draft generated successfully');
        } catch (\Throwable $e) {
            Log::error('AI service generation failed.', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to generate service draft: ' . $e->getMessage(), 500);
        }
    }

    public function generateCaseStudy(Request $request, AiContentService $service)
    {
        set_time_limit(180);

        $validated = $request->validate([
            'projectDescription' => 'required|string|max:6000',
            'clientName' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'goals' => 'nullable|string|max:1000',
            'challenges' => 'nullable|string|max:1000',
            'tone' => 'nullable|string|max:100',
            'technologies' => 'nullable|array',
            'technologies.*' => 'string|max:80',
        ]);

        try {
            $result = $service->generateCaseStudy($validated);
            return $this->successResponse($result, 'Case study draft generated successfully');
        } catch (\Throwable $e) {
            Log::error('AI case study generation failed.', ['error' => $e->getMessage()]);
            return $this->errorResponse('Failed to generate case study draft: ' . $e->getMessage(), 500);
        }
    }
}

