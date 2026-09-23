<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

/**
 * Automatically bust relevant caches when products are created/updated/deleted.
 * This ensures fresh data is shown without needing manual cache clears.
 */
class ProductObserver
{
    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        $this->bustCache($product);
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        $this->bustCache($product);
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        $this->bustCache($product);
    }

    /**
     * Clear all product-related caches.
     */
    private function bustCache(Product $product): void
    {
        // Homepage caches
        Cache::forget('home_featured_products');
        Cache::forget('home_latest_products');
        Cache::forget('home_categories');
        Cache::forget('home_brands');
        
        // About page counts
        Cache::forget('about_product_count');
        
        // Search filters
        Cache::forget('search_filters');
        
        // Shop caches
        Cache::forget('shop_all_categories');
        Cache::forget('shop_brands');
        Cache::forget('shop_categories');
        
        // Header caches
        Cache::forget('header_categories');
        Cache::forget('header_brands');
    }
}
