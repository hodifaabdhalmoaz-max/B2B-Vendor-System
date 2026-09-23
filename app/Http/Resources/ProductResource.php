<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'sku' => $this->SKU,
            'short_description' => $this->short_description,
            'description' => $this->description,
            'regular_price' => (float) $this->regular_price,
            'sale_price' => (float) $this->sale_price,
            'current_price' => (float) $this->current_price,
            'discount_percentage' => $this->discount_percentage,
            'stock_status' => $this->stock_status,
            'quantity' => (int) $this->quantity,
            'featured' => (bool) $this->featured,
            'image' => $this->image ? asset('uploads/products/'.$this->image) : null,
            'gallery' => $this->when($this->images, function () {
                $images = is_array($this->images) ? $this->images : explode(',', $this->images);

                return collect($images)->map(function ($image) {
                    return asset('uploads/products/'.trim($image));
                })->filter()->values();
            }),
            'category' => new CategoryResource($this->whenLoaded('category')),
            'brand' => new BrandResource($this->whenLoaded('brand')),
            'colors' => ColorResource::collection($this->whenLoaded('colors')),
            'sizes' => SizeResource::collection($this->whenLoaded('sizes')),
            'reviews' => $this->when($request->routeIs('api.products.show'),
                ReviewResource::collection($this->whenLoaded('reviews'))
            ),
            'reviews_count' => $this->when($this->reviews_count !== null, $this->reviews_count),
            'average_rating' => $this->when($this->average_rating !== null, (float) $this->average_rating),
            'views' => $this->when($request->user() && $request->user()->utype === 'ADM', $this->views),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [
            'meta' => [
                'currency' => 'YER',
                'currency_symbol' => 'ر.ي',
            ],
        ];
    }
}
