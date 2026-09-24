<?php

namespace App\Services;

use App\Models\Product;

class ResellerProductMediaService
{
    public function description(Product $product): string
    {
        $text = preg_replace('/<(script|style)\b[^>]*>.*?<\/\1>/is', '', $product->description ?? '');
        $text = preg_replace('/<\s*(?:br\s*\/?|\/p|\/div|\/li|\/h[1-6])\s*>/i', "\n", $text);

        return trim(html_entity_decode(strip_tags($text), ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /** Build an allowlist from existing product media, never from request paths. */
    public function images(Product $product): array
    {
        $gallery = $product->images;
        if (! is_array($gallery)) {
            $gallery = explode(',', (string) ($gallery ?: $product->getRawOriginal('images')));
        }
        $images = [];
        foreach (array_filter([$product->image, ...$gallery]) as $filename) {
            if (is_string($filename) && $this->safeFilename($filename)) {
                $images[] = ['path' => 'uploads/products/'.$filename, 'label' => $product->name];
            }
        }
        foreach ($product->colorImages as $image) {
            $prefix = 'uploads/products/colors/';
            if (str_starts_with($image->image_path, $prefix) && $this->safeFilename(substr($image->image_path, strlen($prefix)))) {
                $images[] = ['path' => $image->image_path, 'label' => $image->color?->name ?? $product->name];
            }
        }

        return array_values(collect($images)->unique('path')->all());
    }

    public function downloadPath(Product $product, int $index): string
    {
        $image = $this->images($product)[$index] ?? null;
        abort_unless($image, 404);
        $root = realpath(public_path('uploads/products'));
        $path = realpath(public_path($image['path']));
        abort_unless($root && $path && is_file($path) && str_starts_with($path, $root.DIRECTORY_SEPARATOR), 404);

        return $path;
    }

    private function safeFilename(string $filename): bool
    {
        return (bool) preg_match('/\A[a-zA-Z0-9_][a-zA-Z0-9_.-]*\.(?:webp|png|jpe?g|gif|avif)\z/i', $filename);
    }
}
