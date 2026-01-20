<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use App\Models\CaseStudy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CaseStudyController extends BaseApiController
{
    /**
     * List all case studies.
     * GET /admin/case-studies
     */
    public function index(Request $request): JsonResponse
    {
        $query = CaseStudy::query();

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($industry = $request->get('industry')) {
            $query->where('industry', $industry);
        }

        $perPage = min($request->get('per_page', 15), 100);
        $caseStudies = $query->orderBy('publish_date', 'desc')->paginate($perPage);

        return $this->paginatedResponse($caseStudies->through(fn ($cs) => $cs->toApiResponse()));
    }

    /**
     * Get case study details.
     * GET /admin/case-studies/{id}
     */
    public function show(string $id): JsonResponse
    {
        $caseStudy = CaseStudy::findOrFail($id);

        return $this->successResponse($caseStudy->toApiResponse());
    }

    /**
     * Create case study.
     * POST /admin/case-studies
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'clientName' => 'required|string|max:255',
            'industry' => 'nullable|string|max:100',
            'featuredImage' => 'nullable|string',
            'problem' => 'nullable|string',
            'solution' => 'nullable|string',
            'result' => 'nullable|string',
            'status' => 'sometimes|string|in:draft,published',
            'publishDate' => 'nullable|date',
        ]);

        $caseStudy = CaseStudy::create([
            'title' => $validated['title'],
            'slug' => Str::slug($validated['title']),
            'client_name' => $validated['clientName'],
            'industry' => $validated['industry'] ?? null,
            'featured_image' => $validated['featuredImage'] ?? null,
            'problem' => $validated['problem'] ?? null,
            'solution' => $validated['solution'] ?? null,
            'result' => $validated['result'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'publish_date' => $validated['publishDate'] ?? null,
        ]);

        ActivityLog::log(
            'case_study_created',
            'Case study created',
            "Case study '{$caseStudy->title}' was created",
            $request->user(),
            $caseStudy
        );

        return $this->createdResponse($caseStudy->toApiResponse());
    }

    /**
     * Update case study.
     * PUT /admin/case-studies/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $caseStudy = CaseStudy::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'clientName' => 'sometimes|string|max:255',
            'industry' => 'nullable|string|max:100',
            'featuredImage' => 'nullable|string',
            'problem' => 'nullable|string',
            'solution' => 'nullable|string',
            'result' => 'nullable|string',
            'status' => 'sometimes|string|in:draft,published',
            'publishDate' => 'nullable|date',
        ]);

        $updateData = [];
        if (isset($validated['title'])) {
            $updateData['title'] = $validated['title'];
            $updateData['slug'] = Str::slug($validated['title']);
        }
        if (isset($validated['clientName'])) $updateData['client_name'] = $validated['clientName'];
        if (array_key_exists('industry', $validated)) $updateData['industry'] = $validated['industry'];
        if (array_key_exists('featuredImage', $validated)) $updateData['featured_image'] = $validated['featuredImage'];
        if (array_key_exists('problem', $validated)) $updateData['problem'] = $validated['problem'];
        if (array_key_exists('solution', $validated)) $updateData['solution'] = $validated['solution'];
        if (array_key_exists('result', $validated)) $updateData['result'] = $validated['result'];
        if (isset($validated['status'])) $updateData['status'] = $validated['status'];
        if (array_key_exists('publishDate', $validated)) $updateData['publish_date'] = $validated['publishDate'];

        $caseStudy->update($updateData);

        ActivityLog::log(
            'case_study_updated',
            'Case study updated',
            "Case study '{$caseStudy->title}' was updated",
            $request->user(),
            $caseStudy
        );

        return $this->successResponse($caseStudy->fresh()->toApiResponse());
    }

    /**
     * Delete case study.
     * DELETE /admin/case-studies/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $caseStudy = CaseStudy::findOrFail($id);
        $caseStudyTitle = $caseStudy->title;

        $caseStudy->delete();

        ActivityLog::log(
            'case_study_deleted',
            'Case study deleted',
            "Case study '{$caseStudyTitle}' was deleted",
            $request->user()
        );

        return $this->successResponse(null, 'Case study deleted successfully.');
    }
}
