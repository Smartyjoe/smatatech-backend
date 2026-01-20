<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\ActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image;

class UploadController extends BaseApiController
{
    /**
     * Allowed image mime types.
     */
    private array $allowedImageTypes = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
    ];

    /**
     * Allowed document mime types.
     */
    private array $allowedDocTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];

    /**
     * Upload file.
     * POST /admin/upload
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'folder' => 'nullable|string|max:50',
        ]);

        $file = $request->file('file');
        $folder = $request->get('folder', 'uploads');
        $mimeType = $file->getMimeType();

        // Validate file type
        $allowedTypes = array_merge($this->allowedImageTypes, $this->allowedDocTypes);
        if (!in_array($mimeType, $allowedTypes)) {
            return $this->errorResponse('File type not allowed.', [], 422);
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $path = "{$folder}/" . date('Y/m');

        // Process and optimize images
        if (in_array($mimeType, $this->allowedImageTypes) && $mimeType !== 'image/svg+xml') {
            try {
                $processedImage = $this->processImage($file);
                $fullPath = Storage::disk('public')->put("{$path}/{$filename}", $processedImage);
                $storedPath = "{$path}/{$filename}";
            } catch (\Exception $e) {
                // Fallback to standard upload if image processing fails
                $storedPath = $file->storeAs($path, $filename, 'public');
            }
        } else {
            $storedPath = $file->storeAs($path, $filename, 'public');
        }

        $url = Storage::disk('public')->url($storedPath);

        ActivityLog::log(
            'file_uploaded',
            'File uploaded',
            "File '{$file->getClientOriginalName()}' was uploaded",
            $request->user()
        );

        return $this->successResponse([
            'url' => $url,
            'filename' => $filename,
            'originalName' => $file->getClientOriginalName(),
            'size' => $file->getSize(),
            'mimeType' => $mimeType,
        ]);
    }

    /**
     * Delete file.
     * DELETE /admin/upload
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->validate([
            'url' => 'required|string',
        ]);

        $url = $request->get('url');
        
        // Extract path from URL
        $path = parse_url($url, PHP_URL_PATH);
        $path = str_replace('/storage/', '', $path);

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);

            ActivityLog::log(
                'file_deleted',
                'File deleted',
                "File was deleted from storage",
                $request->user()
            );

            return $this->successResponse(null, 'File deleted successfully.');
        }

        return $this->notFoundResponse('File not found.');
    }

    /**
     * Process and optimize image.
     */
    private function processImage($file): string
    {
        // Check if Intervention Image is available
        if (!class_exists('Intervention\Image\Laravel\Facades\Image')) {
            return file_get_contents($file->getRealPath());
        }

        $image = Image::read($file->getRealPath());
        
        // Resize if too large (max 2000px width/height)
        $image->scaleDown(2000, 2000);
        
        // Optimize quality
        return $image->toJpeg(85)->toString();
    }
}
