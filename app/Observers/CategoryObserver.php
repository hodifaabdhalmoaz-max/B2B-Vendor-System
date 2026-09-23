<?php

namespace App\Observers;

use App\Models\Category;
use Illuminate\Support\Facades\Cache;

/**
 * Automatically bust relevant caches when categories are changed.
 */
class CategoryObserver
{
    public function saved(Category $category): void
    {
        $this->bustCache();
    }

    public function deleted(Category $category): void
    {
        $this->bustCache();
    }

    private function bustCache(): void
    {
        Cache::forget('home_categories');
        Cache::forget('header_categories');
        Cache::forget('shop_all_categories');
        Cache::forget('shop_categories');
        Cache::forget('search_filters');
    }
}
