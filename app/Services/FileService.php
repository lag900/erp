<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileService
{
    protected $imageOptimizer;
    
    public function __construct(ImageOptimizationService $imageOptimizer)
    {
        $this->imageOptimizer = $imageOptimizer;
    }
    
    /**
     * Store a file and optionally delete an old one.
     * Automatically optimizes images.
     */
    public function updateFile(?UploadedFile $file, string $folder, ?string $oldPath = null): ?string
    {
        if (!$file) {
            return $oldPath;
        }

        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

        // Check if file is an image
        if ($this->isImage($file)) {
            $result = $this->imageOptimizer->processImage($file, $folder, false);
            return $result['full'];
        }

        // Regular file storage
        return $file->store($folder, 'public');
    }

    /**
     * Delete a file from disk.
     */
    public function deleteFile(?string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
    
    /**
     * Check if uploaded file is an image
     */
    private function isImage(UploadedFile $file): bool
    {
        $mimeType = $file->getMimeType();
        return str_starts_with($mimeType, 'image/');
    }
}
