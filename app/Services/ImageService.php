<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class ImageService
{
    /**
     * Generate and save brand thumbnail image
     */
    public function generateBrandThumbnail(UploadedFile $image, ?string $imageName = null): string
    {
        $imageName = $imageName ?: $this->generateImageName($image);
        $destinationPath = public_path('uploads/brands');

        // Ensure directory exists
        $this->ensureDirectoryExists($destinationPath);

        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        return $imageName;
    }

    /**
     * Generate and save category thumbnail image
     */
    public function generateCategoryThumbnail(UploadedFile $image, ?string $imageName = null): string
    {
        $imageName = $imageName ?: $this->generateImageName($image);
        $destinationPath = public_path('uploads/categories');

        // Ensure directory exists
        $this->ensureDirectoryExists($destinationPath);

        $img = Image::read($image->path());
        $img->cover(124, 124, 'top');
        $img->resize(124, 124, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        return $imageName;
    }

    /**
     * Generate and save product images (main and thumbnail)
     */
    public function generateProductImages(UploadedFile $image, ?string $imageName = null): string
    {
        $imageName = $imageName ?: $this->generateImageName($image);
        $destinationPath = public_path('uploads/products');
        $thumbnailPath = public_path('uploads/products/thumbnails');

        // Ensure directories exist
        $this->ensureDirectoryExists($destinationPath);
        $this->ensureDirectoryExists($thumbnailPath);

        $img = Image::read($image->path());

        // Save main product image
        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        // Save thumbnail
        $img->resize(104, 104, function ($constraint) {
            $constraint->aspectRatio();
        })->save($thumbnailPath.'/'.$imageName);

        return $imageName;
    }

    public function generateProductColorImage(UploadedFile $image, ?string $imageName = null): string
    {
        $imageName = $imageName ?: $this->generateImageName($image);
        $destinationPath = public_path('uploads/products/colors');

        $this->ensureDirectoryExists($destinationPath);

        $img = Image::read($image->path());
        $img->cover(540, 689, 'top');
        $img->resize(540, 689, function ($constraint) {
            $constraint->aspectRatio();
        })->save($destinationPath.'/'.$imageName);

        return 'uploads/products/colors/'.$imageName;
    }

    /**
     * Process multiple product gallery images
     */
    public function processProductGallery(array $images, ?string $baseTimestamp = null): string
    {
        $baseTimestamp = $baseTimestamp ?: Carbon::now()->timestamp;
        $allowedExtensions = ['jpg', 'png', 'jpeg'];
        $galleryArray = [];
        $counter = 1;

        foreach ($images as $file) {
            if (! ($file instanceof UploadedFile)) {
                continue;
            }

            $extension = $file->getClientOriginalExtension();

            if (in_array(strtolower($extension), $allowedExtensions)) {
                $fileName = $baseTimestamp.'-'.$counter.'.'.$extension;
                $this->generateProductImages($file, $fileName);
                $galleryArray[] = $fileName;
                $counter++;
            }
        }

        return implode(',', $galleryArray);
    }

    /**
     * Delete brand image
     */
    public function deleteBrandImage(string $imageName): bool
    {
        $imagePath = public_path('uploads/brands/'.$imageName);

        if (File::exists($imagePath)) {
            return File::delete($imagePath);
        }

        return true;
    }

    /**
     * Delete category image
     */
    public function deleteCategoryImage(string $imageName): bool
    {
        $imagePath = public_path('uploads/categories/'.$imageName);

        if (File::exists($imagePath)) {
            return File::delete($imagePath);
        }

        return true;
    }

    /**
     * Delete product images (main and thumbnail)
     */
    public function deleteProductImage(string $imageName): bool
    {
        $mainImagePath = public_path('uploads/products/'.$imageName);
        $thumbnailPath = public_path('uploads/products/thumbnails/'.$imageName);

        $deleted = true;

        if (File::exists($mainImagePath)) {
            $deleted = $deleted && File::delete($mainImagePath);
        }

        if (File::exists($thumbnailPath)) {
            $deleted = $deleted && File::delete($thumbnailPath);
        }

        return $deleted;
    }

    /**
     * Delete multiple product gallery images
     */
    public function deleteProductGallery(string $galleryImages): bool
    {
        if (empty($galleryImages)) {
            return true;
        }

        $images = explode(',', $galleryImages);
        $allDeleted = true;

        foreach ($images as $imageName) {
            if (! empty(trim($imageName))) {
                $allDeleted = $allDeleted && $this->deleteProductImage(trim($imageName));
            }
        }

        return $allDeleted;
    }

    public function deleteProductColorImage(string $imagePath): bool
    {
        $fullPath = public_path($imagePath);

        if (File::exists($fullPath)) {
            return File::delete($fullPath);
        }

        return true;
    }

    /**
     * Generate unique image name with timestamp
     */
    protected function generateImageName(UploadedFile $image): string
    {
        return Carbon::now()->timestamp.'.'.$image->extension();
    }

    /**
     * Ensure directory exists, create if not
     */
    protected function ensureDirectoryExists(string $path): void
    {
        if (! File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    /**
     * Validate image file
     */
    public function validateImage(UploadedFile $image, array $allowedExtensions = ['png', 'jpg', 'jpeg'], int $maxSize = 2048): bool
    {
        // Check file extension
        $extension = strtolower($image->getClientOriginalExtension());
        if (! in_array($extension, $allowedExtensions)) {
            return false;
        }

        // Check file size (in KB)
        if ($image->getSize() > ($maxSize * 1024)) {
            return false;
        }

        return true;
    }

    /**
     * Get image dimensions
     */
    public function getImageDimensions(UploadedFile $image): array
    {
        $imageInfo = getimagesize($image->path());

        return [
            'width' => $imageInfo[0] ?? 0,
            'height' => $imageInfo[1] ?? 0,
        ];
    }

    /**
     * Resize image to specific dimensions
     */
    public function resizeImage(UploadedFile $image, int $width, int $height, string $savePath, bool $maintainAspectRatio = true): string
    {
        $imageName = $this->generateImageName($image);
        $fullPath = $savePath.'/'.$imageName;

        // Ensure directory exists
        $this->ensureDirectoryExists($savePath);

        $img = Image::read($image->path());

        if ($maintainAspectRatio) {
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        } else {
            $img->resize($width, $height);
        }

        $img->save($fullPath);

        return $imageName;
    }

    /**
     * Create image with watermark
     */
    public function addWatermark(UploadedFile $image, string $watermarkPath, string $savePath, string $position = 'bottom-right'): string
    {
        $imageName = $this->generateImageName($image);
        $fullPath = $savePath.'/'.$imageName;

        // Ensure directory exists
        $this->ensureDirectoryExists($savePath);

        $img = Image::read($image->path());

        if (File::exists($watermarkPath)) {
            $watermark = Image::read($watermarkPath);
            $img->place($watermark, $position);
        }

        $img->save($fullPath);

        return $imageName;
    }

    /**
     * Generate thumbnails for an image
     */
    public function generateThumbnails(string $imagePath, array $sizes): array
    {
        $thumbnails = [];
        $basePath = dirname($imagePath);
        $filename = pathinfo($imagePath, PATHINFO_FILENAME);
        $extension = pathinfo($imagePath, PATHINFO_EXTENSION);

        foreach ($sizes as $sizeName => $dimensions) {
            $thumbnailName = $filename.'_'.$sizeName.'.'.$extension;
            $thumbnailPath = $basePath.'/thumbnails/'.$thumbnailName;

            // Ensure thumbnail directory exists
            $this->ensureDirectoryExists($basePath.'/thumbnails');

            $img = Image::read(storage_path('app/public/'.$imagePath));
            $img->resize($dimensions[0], $dimensions[1], function ($constraint) {
                $constraint->aspectRatio();
            })->save(storage_path('app/public/'.$thumbnailPath));

            $thumbnails[$sizeName] = $thumbnailPath;
        }

        return $thumbnails;
    }

    /**
     * Optimize image file size
     */
    public function optimizeImage(string $imagePath, int $quality = 85): string
    {
        $fullPath = storage_path('app/public/'.$imagePath);

        if (! File::exists($fullPath)) {
            throw new \Exception("Image file not found: {$imagePath}");
        }

        $img = Image::read($fullPath);

        // Apply optimization based on file type
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        switch ($extension) {
            case 'jpg':
            case 'jpeg':
                $img->toJpeg($quality);
                break;
            case 'png':
                $img->toPng();
                break;
            case 'webp':
                $img->toWebp($quality);
                break;
        }

        $img->save($fullPath);

        return $imagePath;
    }
}
