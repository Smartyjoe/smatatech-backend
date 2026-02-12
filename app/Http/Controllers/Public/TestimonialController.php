<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $featured = $request->input('featured');

        $query = Testimonial::where('status', 'active')
            ->latest();

        if ($featured !== null) {
            $query->where('featured', filter_var($featured, FILTER_VALIDATE_BOOLEAN));
        }

        $testimonials = $query->get();

        return $this->successResponse($testimonials);
    }
}
