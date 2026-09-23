<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Observers\ProductObserver;
use App\Observers\CategoryObserver;
use App\Observers\BrandObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Prevent lazy loading in local development to avoid N+1 query problems
        \Illuminate\Database\Eloquent\Model::preventLazyLoading(!app()->isProduction());

        // Register cache-busting observers
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        Brand::observe(BrandObserver::class);

        // Load translation helper
        require_once app_path('Helpers/TranslationHelper.php');

        // Set locale from session or default to Arabic
        $locale = session('locale', 'ar');
        app()->setLocale($locale);

        // Share translation helper with all views
        view()->share('trans', \App\Helpers\TranslationHelper::class);

        // Share common translations with all views
        view()->composer('*', function ($view) {
            $view->with('commonTrans', \App\Helpers\TranslationHelper::getCommonTranslations());
            $view->with('isRtl', \App\Helpers\TranslationHelper::isRtl());
            $view->with('direction', \App\Helpers\TranslationHelper::getDirection());
        });

        // Share categories and brands with header
        View::composer('layouts.partials.header', \App\Http\View\Composers\HeaderComposer::class);
    }
}
