<?php

namespace App\Http\View\Composers;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HeaderComposer
{
    /**
     * Bind data to the view.
     * Cached aggressively — categories/brands rarely change.
     *
     * @param  \Illuminate\View\View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $categories = Cache::remember('header_categories', 1800, function () {
            return Category::select('id', 'name', 'slug', 'image')
                ->orderBy('name', 'ASC')
                ->get();
        });

        $brands = Cache::remember('header_brands', 1800, function () {
            return Brand::select('id', 'name', 'slug', 'image')
                ->orderBy('name', 'ASC')
                ->get();
        });
        
        $view->with(compact('categories', 'brands'));
    }
}
