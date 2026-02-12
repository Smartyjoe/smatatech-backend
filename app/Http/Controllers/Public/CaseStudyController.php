<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 12);
        $industry = $request->input('industry');

        $query = CaseStudy::where('status', 'published')
            ->latest();

        if ($industry) {
            $query->where('industry', $industry);
        }

        $caseStudies = $query->paginate($perPage);

        return $this->paginatedResponse($caseStudies);
    }

    public function show($slug)
    {
        $caseStudy = CaseStudy::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return $this->successResponse($caseStudy);
    }
}
