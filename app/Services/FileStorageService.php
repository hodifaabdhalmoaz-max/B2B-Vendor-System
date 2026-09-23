<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FileStorageService
{
    /**
     * Allowed file types and their configurations
     */
    const ALLOWED_TYPES = [
        'images' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'max_size' => 5 * 1024 * 1024, // 5MB
            'path' => 'uploads/images',
        ],
        'products' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_size' => 3 * 1024 * 1024, // 3MB
            'path' => 'uploads/products',
        ],
        'categories' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_size' => 2 * 1024 * 1024, // 2MB
            'path' => 'uploads/categories',
        ],
        'brands' => [
            'extensions' => ['jpg', 'jpeg', 'png', 'webp'],
            'max_size' => 2 * 1024 * 1024, // 2MB
            'path' => 'uploads/brands',
        ],
        'documents' => [
            'extensions' => ['pdf', 'doc', 'docx', 'txt'],
            'max_size' => 10 * 1024 * 1024, // 10MB
            'path' => 'uploads/documents',
        ],
    ];

    /**
     * Store uploaded file
     *
     * @param UploadedFile $file
     * @param string $type
     * @param string|null $customName
     * @return array
     */
    public function store(UploadedFile $file, string $type, ?string $customName = null): array
    {
        try {
            // Validate file type
            $this->validateFile($file, $type);
            
            // Generate unique filename
            $filename = $this->generateFilename($file, $customName);
            
            // Get storage path
            $path = self::ALLOWED_TYPES[$type]['path'];
            
            // Store file
            $storedPath = $file->storeAs($path, $filename, 'public');
            
            // Get file info
            $fileInfo = [
                'original_name' => $file->getClientOriginalName(),
                'filename' => $filename,
                'path' => $storedPath,
                'url' => Storage::disk('public')->url($storedPath),
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'type' => $type,
                'uploaded_at' => now()->toISOString(),
            ];
            
            Log::info('File uploaded successfully', $fileInfo);
            
            return $fileInfo;
            
        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'error' => $e->getMessage(),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'type' => $type,
            ]);
            
            throw $e;
        }
    }

    /**
     * Delete file
     *
     * @param string $path
     * @return bool
     */
    public function delete(string $path): bool
    {
        try {
            if (Storage::disk('public')->exists($path)) {
                $deleted = Storage::disk('public')->delete($path);
                
                if ($deleted) {
                    Log::info('File deleted successfully', ['path' => $path]);
                } else {
                    Log::warning('Failed to delete file', ['path' => $path]);
                }
                
                return $deleted;
            }
            
            Log::warning('File not found for deletion', ['path' => $path]);
            return false;
            
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            
            return false;
        }
    }

    /**
     * Validate uploaded file
     *
     * @param UploadedFile $file
     * @param string $type
     * @throws \InvalidArgumentException
     */
    protected function validateFile(UploadedFile $file, string $type): void
    {
        if (!isset(self::ALLOWED_TYPES[$type])) {
            throw new \InvalidArgumentException("Invalid file type: {$type}");
        }
        
        $config = self::ALLOWED_TYPES[$type];
        
        // Check file extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $config['extensions'])) {
            throw new \InvalidArgumentException(
                "Invalid file extension. Allowed: " . implode(', ', $config['extensions'])
            );
        }
        
        // Check file size
        if ($file->getSize() > $config['max_size']) {
            $maxSizeMB = round($config['max_size'] / (1024 * 1024), 2);
            throw new \InvalidArgumentException(
                "File size exceeds maximum allowed size of {$maxSizeMB}MB"
            );
        }
        
        // Check if file is valid
        if (!$file->isValid()) {
            throw new \InvalidArgumentException("Invalid file upload");
        }
    }

    /**
     * Generate unique filename
     *
     * @param UploadedFile $file
     * @param string|null $customName
     * @return string
     */
    protected function generateFilename(UploadedFile $file, ?string $customName = null): string
    {
        $extension = $file->getClientOriginalExtension();
        
        if ($customName) {
            $name = Str::slug($customName);
        } else {
            $name = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        }
        
        // Add timestamp and random string for uniqueness
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        
        return "{$name}_{$timestamp}_{$random}.{$extension}";
    }

    /**
     * Get file info
     *
     * @param string $path
     * @return array|null
     */
    public function getFileInfo(string $path): ?array
    {
        try {
            if (!Storage::disk('public')->exists($path)) {
                return null;
            }
            
            return [
                'path' => $path,
                'url' => Storage::disk('public')->url($path),
                'size' => Storage::disk('public')->size($path),
                'last_modified' => Storage::disk('public')->lastModified($path),
                'exists' => true,
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to get file info', [
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Clean up old files
     *
     * @param string $type
     * @param int $days
     * @return int
     */
    public function cleanupOldFiles(string $type, int $days = 30): int
    {
        try {
            if (!isset(self::ALLOWED_TYPES[$type])) {
                throw new \InvalidArgumentException("Invalid file type: {$type}");
            }
            
            $path = self::ALLOWED_TYPES[$type]['path'];
            $files = Storage::disk('public')->files($path);
            $deletedCount = 0;
            $cutoffTime = now()->subDays($days)->timestamp;
            
            foreach ($files as $file) {
                $lastModified = Storage::disk('public')->lastModified($file);
                
                if ($lastModified < $cutoffTime) {
                    if (Storage::disk('public')->delete($file)) {
                        $deletedCount++;
                    }
                }
            }
            
            Log::info('Old files cleanup completed', [
                'type' => $type,
                'deleted_count' => $deletedCount,
                'days' => $days,
            ]);
            
            return $deletedCount;
            
        } catch (\Exception $e) {
            Log::error('File cleanup failed', [
                'type' => $type,
                'error' => $e->getMessage(),
            ]);
            
            return 0;
        }
    }

    /**
     * Get storage statistics
     *
     * @return array
     */
    public function getStorageStats(): array
    {
        try {
            $stats = [
                'total_files' => 0,
                'total_size' => 0,
                'by_type' => [],
            ];
            
            foreach (self::ALLOWED_TYPES as $type => $config) {
                $path = $config['path'];
                $files = Storage::disk('public')->files($path);
                $typeSize = 0;
                
                foreach ($files as $file) {
                    $typeSize += Storage::disk('public')->size($file);
                }
                
                $stats['by_type'][$type] = [
                    'count' => count($files),
                    'size' => $typeSize,
                    'size_formatted' => $this->formatBytes($typeSize),
                ];
                
                $stats['total_files'] += count($files);
                $stats['total_size'] += $typeSize;
            }
            
            $stats['total_size_formatted'] = $this->formatBytes($stats['total_size']);
            
            return $stats;
            
        } catch (\Exception $e) {
            Log::error('Failed to get storage statistics', [
                'error' => $e->getMessage(),
            ]);
            
            return [
                'error' => 'Failed to retrieve storage statistics',
            ];
        }
    }

    /**
     * Format bytes to human readable format
     *
     * @param int $bytes
     * @return string
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}
