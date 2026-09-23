<?php

namespace App\Providers;

use App\Services\TranslationService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TranslationService::class, function ($app) {
            return new TranslationService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Register a custom Blade directive for translations
        Blade::directive('t', function ($expression) {
            return "<?php echo app(\\App\\Services\\TranslationService::class)->get({$expression}); ?>";
        });
        
        // Share most used translations with all views
        view()->composer('*', function ($view) {
            $translationService = app(TranslationService::class);
            $view->with('mostUsedTranslations', $translationService->getMostUsed());
        });
    }
}
