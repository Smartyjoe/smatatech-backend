<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
    use ApiResponse;

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'type' => 'nullable|string|in:image,document,logo,favicon,blog,service,case-study,testimonial,brand',
        ]);

        $file = $request->file('file');
        $type = $request->input('type', 'image');

        // Validate file type based on upload type
        if (in_array($type, ['image', 'logo', 'favicon', 'blog', 'service', 'case-study', 'testimonial', 'brand'])) {
            $request->validate([
                'file' => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:5120', // 5MB for images
            ]);
        }

        try {
            // Generate unique filename
            $extension = $file->getClientOriginalExtension();
            $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) 
                      . '-' . time() 
                      . '.' . $extension;

            // Store file in organized structure
            $path = $file->storeAs("uploads/{$type}", $filename, 'public');

            // Generate URL
            $url = Storage::url($path);

            return $this->successResponse([
                'filename' => $filename,
                'path' => $path,
                'url' => $url,
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
            ], 'File uploaded successfully', 201);

        } catch (\Exception $e) {
            return $this->errorResponse('File upload failed: ' . $e->getMessage(), 500);
        }
    }
}
