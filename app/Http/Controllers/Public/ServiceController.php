<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Traits\ApiResponse;

class ServiceController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $services = Service::where('status', 'active')
            ->orderBy('order', 'asc')
            ->get();

        return $this->successResponse($services);
    }

    public function show($slug)
    {
        $service = Service::where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        return $this->successResponse($service);
    }
}
