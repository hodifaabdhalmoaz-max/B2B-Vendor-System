<?php

// app/Providers/RateLimitServiceProvider.php
// Centralized rate limiter definitions for all route groups

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services — define all named rate limiters here.
     */
    public function boot(): void
    {
        $this->configureApiLimiters();
        $this->configureWebLimiters();
    }

    /**
     * Define API-specific rate limiters.
     */
    protected function configureApiLimiters(): void
    {
        // General API limiter — higher for authenticated users
        RateLimiter::for('api', function (Request $request) {
            $config = config('throttle.api.default');
            $limit = $config['max_attempts'];

            // Authenticated users get a multiplier
            if ($request->user()) {
                $limit = (int) ($limit * config('throttle.auth_multiplier', 2.0));
            }

            return Limit::perMinute(maxAttempts: $limit)
                ->by($request->user()?->id ?: $request->ip())
                ->response(function (Request $request, array $headers) {
                    // Return structured JSON for API requests
                    return response()->json([
                        'success' => false,
                        'message' => 'تم تجاوز عدد الطلبات المسموح. حاول مرة أخرى لاحقاً.',
                        'error' => 'rate_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? 60,
                    ], 429, $headers);
                });
        });

        // Auth endpoints — very strict
        RateLimiter::for('api-auth', function (Request $request) {
            $config = config('throttle.api.auth');

            return Limit::perMinutes(
                decayMinutes: $config['decay_minutes'],
                maxAttempts: $config['max_attempts'],
            )
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'success' => false,
                        'message' => 'محاولات كثيرة. انتظر قبل المحاولة مجدداً.',
                        'error' => 'auth_rate_limit_exceeded',
                        'retry_after' => $headers['Retry-After'] ?? 300,
                    ], 429, $headers);
                });
        });

        // Products API — generous for catalog browsing
        RateLimiter::for('api-products', function (Request $request) {
            $config = config('throttle.api.products');

            return Limit::perMinute(maxAttempts: $config['max_attempts'])
                ->by($request->user()?->id ?: $request->ip());
        });
    }

    /**
     * Define web-specific rate limiters.
     */
    protected function configureWebLimiters(): void
    {
        // Public browsing — generous limit
        RateLimiter::for('web-public', function (Request $request) {
            $config = config('throttle.web.public');
            $limit = $config['max_attempts'];

            // Authenticated users get higher limits
            if ($request->user()) {
                $limit = (int) ($limit * config('throttle.auth_multiplier', 2.0));
            }

            return Limit::perMinute(maxAttempts: $limit)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Cart operations
        RateLimiter::for('web-cart', function (Request $request) {
            $config = config('throttle.web.cart');
            $limit = $config['max_attempts'];

            if ($request->user()) {
                $limit = (int) ($limit * config('throttle.auth_multiplier', 2.0));
            }

            return Limit::perMinute(maxAttempts: $limit)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Checkout — moderate, authenticated only
        RateLimiter::for('web-checkout', function (Request $request) {
            $config = config('throttle.web.checkout');
            // Always keyed by user ID since checkout requires auth
            return Limit::perMinute(maxAttempts: $config['max_attempts'])
                ->by($request->user()?->id ?: $request->ip());
        });

        // Search — moderate to prevent abuse
        RateLimiter::for('web-search', function (Request $request) {
            $config = config('throttle.web.search');
            $limit = $config['max_attempts'];

            if ($request->user()) {
                $limit = (int) ($limit * config('throttle.auth_multiplier', 2.0));
            }

            return Limit::perMinute(maxAttempts: $limit)
                ->by($request->user()?->id ?: $request->ip());
        });

        // Wishlist
        RateLimiter::for('web-wishlist', function (Request $request) {
            $config = config('throttle.web.wishlist');

            return Limit::perMinute(maxAttempts: $config['max_attempts'])
                ->by($request->user()?->id ?: $request->ip());
        });

        // User dashboard
        RateLimiter::for('web-user', function (Request $request) {
            $config = config('throttle.web.user_dashboard');

            return Limit::perMinute(maxAttempts: $config['max_attempts'])
                ->by($request->user()?->id ?: $request->ip());
        });

        // Admin panel — very generous
        RateLimiter::for('web-admin', function (Request $request) {
            $config = config('throttle.web.admin');
            $limit = (int) ($config['max_attempts'] * config('throttle.admin_multiplier', 5.0));

            return Limit::perMinute(maxAttempts: $limit)
                ->by($request->user()?->id ?: $request->ip());
        });
    }
}
