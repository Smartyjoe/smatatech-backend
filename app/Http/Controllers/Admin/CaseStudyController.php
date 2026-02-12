<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CaseStudy;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class CaseStudyController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $search = $request->input('search');
        $status = $request->input('status');
        $industry = $request->input('industry');

        $query = CaseStudy::query()->latest();

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('client', 'like', "%{$search}%");
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($industry) {
            $query->where('industry', $industry);
        }

        $caseStudies = $query->paginate($perPage);

        return $this->paginatedResponse($caseStudies);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:case_studies,slug',
            'clientName' => 'required|string|max:255',
            'industry' => 'nullable|string',
            'duration' => 'nullable|string',
            'year' => 'nullable|string',
            'featuredImage' => 'nullable|string',
            'shortDescription' => 'nullable|string',
            'challengeOverview' => 'nullable|string',
            'challengePoints' => 'nullable|array',
            'solutionOverview' => 'nullable|string',
            'solutionPoints' => 'nullable|array',
            'results' => 'nullable|array',
            'processSteps' => 'nullable|array',
            'technologies' => 'nullable|array',
            'testimonialQuote' => 'nullable|string',
            'testimonialAuthor' => 'nullable|string',
            'testimonialRole' => 'nullable|string',
            'status' => 'required|in:published,draft',
            'publishDate' => 'nullable|string',
            'metaTitle' => 'nullable|string',
            'metaDescription' => 'nullable|string',
        ]);

        // Combine challenge and solution data for database
        $challenge = $validated['challengeOverview'] ?? '';
        if (!empty($validated['challengePoints'])) {
            $challenge .= "\n\n" . implode("\n", array_filter($validated['challengePoints']));
        }

        $solution = $validated['solutionOverview'] ?? '';
        if (!empty($validated['solutionPoints'])) {
            $solution .= "\n\n" . implode("\n", array_filter($validated['solutionPoints']));
        }

        // Format testimonial
        $testimonial = '';
        if (!empty($validated['testimonialQuote'])) {
            $testimonial = $validated['testimonialQuote'];
            if (!empty($validated['testimonialAuthor'])) {
                $testimonial .= "\n- " . $validated['testimonialAuthor'];
                if (!empty($validated['testimonialRole'])) {
                    $testimonial .= ", " . $validated['testimonialRole'];
                }
            }
        }

        $dbData = [
            'title' => $validated['title'],
            'slug' => $validated['slug'] ?? null,
            'client' => $validated['clientName'],
            'industry' => $validated['industry'] ?? null,
            'challenge' => $challenge ?: null,
            'solution' => $solution ?: null,
            'results' => !empty($validated['results']) ? json_encode($validated['results']) : null,
            'testimonial' => $testimonial ?: null,
            'technologies' => $validated['technologies'] ?? null,
            'image' => $validated['featuredImage'] ?? null,
            'gallery' => null, // Not implemented yet
            'status' => $validated['status'],
            'meta_title' => $validated['metaTitle'] ?? null,
            'meta_description' => $validated['metaDescription'] ?? null,
        ];

        // Filter out only truly null values, keep empty strings
        $dbData = array_filter($dbData, fn($value) => $value !== null);
        
        $caseStudy = CaseStudy::create($dbData);

        return $this->successResponse($caseStudy, 'Case study created successfully', 201);
    }

    public function show($id)
    {
        $caseStudy = CaseStudy::findOrFail($id);
        return $this->successResponse($caseStudy);
    }

    public function update(Request $request, $id)
    {
        $caseStudy = CaseStudy::findOrFail($id);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|unique:case_studies,slug,' . $id,
            'clientName' => 'sometimes|required|string|max:255',
            'industry' => 'nullable|string',
            'duration' => 'nullable|string',
            'year' => 'nullable|string',
            'featuredImage' => 'nullable|string',
            'shortDescription' => 'nullable|string',
            'challengeOverview' => 'nullable|string',
            'challengePoints' => 'nullable|array',
            'solutionOverview' => 'nullable|string',
            'solutionPoints' => 'nullable|array',
            'results' => 'nullable|array',
            'processSteps' => 'nullable|array',
            'technologies' => 'nullable|array',
            'testimonialQuote' => 'nullable|string',
            'testimonialAuthor' => 'nullable|string',
            'testimonialRole' => 'nullable|string',
            'status' => 'sometimes|required|in:published,draft',
            'publishDate' => 'nullable|string',
            'metaTitle' => 'nullable|string',
            'metaDescription' => 'nullable|string',
        ]);

        $dbData = [];
        
        if (isset($validated['title'])) $dbData['title'] = $validated['title'];
        if (isset($validated['slug'])) $dbData['slug'] = $validated['slug'];
        if (isset($validated['clientName'])) $dbData['client'] = $validated['clientName'];
        if (isset($validated['industry'])) $dbData['industry'] = $validated['industry'];
        if (isset($validated['status'])) $dbData['status'] = $validated['status'];
        if (isset($validated['featuredImage'])) $dbData['image'] = $validated['featuredImage'];
        if (isset($validated['metaTitle'])) $dbData['meta_title'] = $validated['metaTitle'];
        if (isset($validated['metaDescription'])) $dbData['meta_description'] = $validated['metaDescription'];
        
        // Combine challenge
        if (isset($validated['challengeOverview']) || isset($validated['challengePoints'])) {
            $challenge = $validated['challengeOverview'] ?? '';
            if (!empty($validated['challengePoints'])) {
                $challenge .= "\n\n" . implode("\n", array_filter($validated['challengePoints']));
            }
            $dbData['challenge'] = $challenge ?: null;
        }
        
        // Combine solution
        if (isset($validated['solutionOverview']) || isset($validated['solutionPoints'])) {
            $solution = $validated['solutionOverview'] ?? '';
            if (!empty($validated['solutionPoints'])) {
                $solution .= "\n\n" . implode("\n", array_filter($validated['solutionPoints']));
            }
            $dbData['solution'] = $solution ?: null;
        }
        
        // Format results
        if (isset($validated['results'])) {
            $dbData['results'] = !empty($validated['results']) ? json_encode($validated['results']) : null;
        }
        
        // Format testimonial
        if (isset($validated['testimonialQuote']) || isset($validated['testimonialAuthor'])) {
            $testimonial = '';
            if (!empty($validated['testimonialQuote'])) {
                $testimonial = $validated['testimonialQuote'];
                if (!empty($validated['testimonialAuthor'])) {
                    $testimonial .= "\n- " . $validated['testimonialAuthor'];
                    if (!empty($validated['testimonialRole'])) {
                        $testimonial .= ", " . $validated['testimonialRole'];
                    }
                }
            }
            $dbData['testimonial'] = $testimonial ?: null;
        }
        
        // Technologies
        if (isset($validated['technologies'])) {
            $dbData['technologies'] = $validated['technologies'];
        }

        $caseStudy->update($dbData);

        return $this->successResponse($caseStudy, 'Case study updated successfully');
    }

    public function destroy($id)
    {
        $caseStudy = CaseStudy::findOrFail($id);
        $caseStudy->delete();

        return $this->successResponse(null, 'Case study deleted successfully');
    }
}
