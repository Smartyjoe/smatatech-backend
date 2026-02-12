<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiBlogService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class AiBlogController extends Controller
{
    use ApiResponse;

    public function generate(Request $request, AiBlogService $service)
    {
        set_time_limit(180);

        $validated = $request->validate([
            'topic' => 'required|string|max:255',
            'tone' => 'nullable|string|max:50',
            'keywords' => 'nullable|array',
            'keywords.*' => 'string|max:50',
        ]);

        $result = $service->generate($validated);

        return $this->successResponse($result, 'Blog generated successfully');
    }
}
