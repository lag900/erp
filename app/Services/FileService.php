<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class FileService
{
    /**
     * Store a file and optionally delete an old one.
     */
    public function updateFile(?UploadedFile $file, string $folder, ?string $oldPath = null): ?string
    {
        if (!$file) {
            return $oldPath;
        }

        if ($oldPath) {
            $this->deleteFile($oldPath);
        }

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
}
