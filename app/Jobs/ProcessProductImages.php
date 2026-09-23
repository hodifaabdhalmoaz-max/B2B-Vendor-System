<?php

namespace App\Jobs;

use App\Models\Product;
use App\Services\ImageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessProductImages implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public $product;
    public $imagePaths;
    public $tries = 2;
    public $timeout = 300; // 5 minutes

    /**
     * Create a new job instance.
     */
    public function __construct(Product $product, array $imagePaths)
    {
        $this->product = $product;
        $this->imagePaths = $imagePaths;
        $this->onQueue('images');
    }

    /**
     * Execute the job.
     */
    public function handle(ImageService $imageService): void
    {
        try {
            $processedImages = [];

            foreach ($this->imagePaths as $imagePath) {
                if (!Storage::disk('public')->exists($imagePath)) {
                    Log::warning('Image file not found', ['path' => $imagePath]);
                    continue;
                }

                // Generate thumbnails
                $thumbnails = $imageService->generateThumbnails($imagePath, [
                    'small' => [150, 150],
                    'medium' => [300, 300],
                    'large' => [600, 600],
                ]);

                // Optimize original image
                $optimizedPath = $imageService->optimizeImage($imagePath);

                $processedImages[] = [
                    'original' => $optimizedPath,
                    'thumbnails' => $thumbnails,
                ];
            }

            // Update product with processed image data
            $this->product->update([
                'processed_images' => json_encode($processedImages),
                'images_processed_at' => now(),
            ]);

            Log::info('Product images processed successfully', [
                'product_id' => $this->product->id,
                'images_count' => count($processedImages),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process product images', [
                'product_id' => $this->product->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessProductImages failed permanently', [
            'product_id' => $this->product->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark product as having failed image processing
        $this->product->update([
            'image_processing_failed' => true,
            'image_processing_error' => $exception->getMessage(),
        ]);
    }
}
