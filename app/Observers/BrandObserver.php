<?php

namespace App\Observers;

use App\Models\Brand;
use Illuminate\Support\Facades\Cache;

/**
 * Automatically bust relevant caches when brands are changed.
 */
class BrandObserver
{
    public function saved(Brand $brand): void
    {
        $this->bustCache();
    }

    public function deleted(Brand $brand): void
    {
        $this->bustCache();
    }

    private function bustCache(): void
    {
        Cache::forget('home_brands');
        Cache::forget('header_brands');
        Cache::forget('shop_brands');
        Cache::forget('search_filters');
    }
}
