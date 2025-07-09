<?php

namespace App\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HasFileUploads
{
    /**
     * Upload a file to storage.
     */
    public function uploadFile(UploadedFile $file, string $directory, ?string $disk = null): array
    {
        $disk = $disk ?? config('filesystems.default');

        // Generate unique filename
        $filename = $this->generateUniqueFilename($file);

        // Store file
        $path = $file->storeAs($directory, $filename, $disk);

        return [
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
        ];
    }

    /**
     * Delete a file from storage.
     */
    public function deleteFile(string $filePath, ?string $disk = null): bool
    {
        $disk = $disk ?? config('filesystems.default');

        if (Storage::disk($disk)->exists($filePath)) {
            return Storage::disk($disk)->delete($filePath);
        }

        return false;
    }

    /**
     * Get file URL.
     */
    public function getFileUrl(string $filePath, ?string $disk = null): string
    {
        $disk = $disk ?? config('filesystems.default');

        if ($disk === 'public') {
            return asset("storage/{$filePath}");
        }

        return Storage::disk($disk)->url($filePath);
    }

    /**
     * Generate a unique filename.
     */
    protected function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        // Sanitize filename
        $name = Str::slug($name);

        // Add timestamp and random string for uniqueness
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);

        return "{$name}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Format file size in human readable format.
     */
    public function formatFileSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2).' '.$units[$i];
    }

    /**
     * Validate file type.
     */
    public function validateFileType(UploadedFile $file, array $allowedTypes): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();

        return in_array($extension, $allowedTypes) || in_array($mimeType, $allowedTypes);
    }

    /**
     * Validate file size.
     */
    public function validateFileSize(UploadedFile $file, int $maxSizeInMB): bool
    {
        $maxSizeInBytes = $maxSizeInMB * 1024 * 1024;

        return $file->getSize() <= $maxSizeInBytes;
    }

    /**
     * Get file information.
     */
    public function getFileInfo(string $filePath, ?string $disk = null): ?array
    {
        $disk = $disk ?? config('filesystems.default');

        if (! Storage::disk($disk)->exists($filePath)) {
            return null;
        }

        $size = Storage::disk($disk)->size($filePath);
        $lastModified = Storage::disk($disk)->lastModified($filePath);
        $mimeType = Storage::disk($disk)->mimeType($filePath);

        return [
            'path' => $filePath,
            'size' => $size,
            'size_formatted' => $this->formatFileSize($size),
            'last_modified' => $lastModified,
            'mime_type' => $mimeType,
            'url' => $this->getFileUrl($filePath, $disk),
        ];
    }
}
