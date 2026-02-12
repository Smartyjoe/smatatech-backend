<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Traits\ApiResponse;

class BrandController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $brands = Brand::where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();

        return $this->successResponse($brands);
    }
}
