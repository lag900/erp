<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image;

class ImageOptimizationService
{
    // Maximum dimensions for different image types
    private const MAX_WIDTH_FULL = 1200;
    private const MAX_WIDTH_THUMBNAIL = 400;
    private const MAX_WIDTH_CARD = 600;
    
    // Quality settings (0-100)
    private const QUALITY_WEBP = 85;
    private const QUALITY_JPG = 80;
    
    /**
     * Process and optimize uploaded image
     * Returns array with paths: ['full' => '...', 'thumbnail' => '...']
     */
    public function processImage(UploadedFile $file, string $folder, bool $generateThumbnail = true): array
    {
        $paths = [];
        
        // Generate unique filename
        $filename = time() . '_' . uniqid();
        
        // Process full-size optimized image
        $paths['full'] = $this->optimizeAndStore($file, $folder, $filename, self::MAX_WIDTH_FULL);
        
        // Generate thumbnail if requested
        if ($generateThumbnail) {
            $paths['thumbnail'] = $this->optimizeAndStore($file, $folder . '/thumbnails', $filename . '_thumb', self::MAX_WIDTH_THUMBNAIL);
        }
        
        return $paths;
    }
    
    /**
     * Optimize and store a single image
     */
    private function optimizeAndStore(UploadedFile $file, string $folder, string $filename, int $maxWidth): string
    {
        // Load image
        $image = Image::read($file->getRealPath());
        
        // Get original dimensions
        $width = $image->width();
        $height = $image->height();
        
        // Resize if needed (maintain aspect ratio)
        if ($width > $maxWidth) {
            $image->scale(width: $maxWidth);
        }
        
        // Determine format (prefer WebP for better compression)
        $extension = 'webp';
        $storagePath = "{$folder}/{$filename}.{$extension}";
        
        // Encode to WebP with optimization
        $encoded = $image->toWebp(quality: self::QUALITY_WEBP);
        
        // Store in public disk
        Storage::disk('public')->put($storagePath, (string) $encoded);
        
        return $storagePath;
    }
    
    /**
     * Process and replace existing image
     */
    public function updateImage(?UploadedFile $file, string $folder, ?string $oldPath = null, ?string $oldThumbnailPath = null): ?array
    {
        if (!$file) {
            return $oldPath ? ['full' => $oldPath, 'thumbnail' => $oldThumbnailPath] : null;
        }
        
        // Delete old images
        if ($oldPath) {
            $this->deleteImage($oldPath);
        }
        if ($oldThumbnailPath) {
            $this->deleteImage($oldThumbnailPath);
        }
        
        // Process new image
        return $this->processImage($file, $folder);
    }
    
    /**
     * Delete image from storage
     */
    public function deleteImage(?string $path): bool
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->delete($path);
        }
        return false;
    }
    
    /**
     * Optimize existing image in storage (for batch optimization)
     */
    public function optimizeExistingImage(string $path, int $maxWidth = self::MAX_WIDTH_FULL): bool
    {
        if (!Storage::disk('public')->exists($path)) {
            return false;
        }
        
        try {
            $fullPath = Storage::disk('public')->path($path);
            $image = Image::read($fullPath);
            
            // Resize if needed
            if ($image->width() > $maxWidth) {
                $image->scale(width: $maxWidth);
            }
            
            // Re-encode with optimization
            $encoded = $image->toWebp(quality: self::QUALITY_WEBP);
            
            // Overwrite original
            Storage::disk('public')->put($path, (string) $encoded);
            
            return true;
        } catch (\Exception $e) {
            \Log::error("Image optimization failed for {$path}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get optimized image URL with fallback
     */
    public function getImageUrl(?string $path, bool $thumbnail = false): ?string
    {
        if (!$path) {
            return null;
        }
        
        return asset('storage/' . $path);
    }
}
