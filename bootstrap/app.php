<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global middleware
        $middleware->use([
            \App\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
            \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
            \App\Http\Middleware\TrimStrings::class,
            \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        ]);

        // Web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\XssSanitizer::class,
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SetSeoMetadata::class,
            // BotProtection runs globally on all web requests — lightweight check
            \App\Http\Middleware\BotProtection::class,
        ]);

        // API middleware group
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // Use the named 'api' limiter defined in RateLimitServiceProvider
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\PerformanceMonitoring::class,
        ]);

        // REMOVED: throttleApi('60,1') — now handled by named limiters in RateLimitServiceProvider
        // REMOVED: throttleWithRedis() — not compatible when CACHE_STORE=file

        // Custom middleware aliases
        $middleware->alias([
            'auth' => \App\Http\Middleware\Authenticate::class,
            'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
            'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
            'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
            'can' => \Illuminate\Auth\Middleware\Authorize::class,
            'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
            'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
            'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
            'signed' => \App\Http\Middleware\ValidateSignature::class,
            'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'auth.admin' => \App\Http\Middleware\AuthAdmin::class,
            'xss.sanitizer' => \App\Http\Middleware\XssSanitizer::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            // New smart throttle middleware — replaces the old StrictRateLimit + throttle combo
            'smart.throttle' => \App\Http\Middleware\SmartThrottle::class,
            // Old aliases kept for backward compatibility
            'cache.response' => \App\Http\Middleware\ResponseCacheMiddleware::class,
            'rate.limit.strict' => \App\Http\Middleware\StrictRateLimit::class,
            'login.protection' => \App\Http\Middleware\LoginAttemptProtection::class,
            '2fa' => \App\Http\Middleware\TwoFactorAuthentication::class,
            'bot.protection' => \App\Http\Middleware\BotProtection::class,
        ]);
    })
    ->withProviders([
        // Security Provider
        \App\Providers\SecurityServiceProvider::class,
        // Rate Limiting Provider — defines all named rate limiters
        \App\Providers\RateLimitServiceProvider::class,
    ])
    ->withCommands([
        \App\Console\Commands\SecurityAudit::class,
        \App\Console\Commands\SecureBackup::class,
        \App\Console\Commands\OptimizePerformance::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        // Custom handler for 429 Too Many Requests — renders a user-friendly page
        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) {
            // Extract Retry-After from the exception headers
            $retryAfter = $e->getHeaders()['Retry-After'] ?? 60;

            // API requests get a structured JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم تجاوز عدد الطلبات المسموح. حاول مرة أخرى لاحقاً.',
                    'error' => 'too_many_requests',
                    'retry_after' => (int) $retryAfter,
                ], 429, [
                    'Retry-After' => $retryAfter,
                    'X-RateLimit-Limit' => $e->getHeaders()['X-RateLimit-Limit'] ?? 0,
                    'X-RateLimit-Remaining' => 0,
                ]);
            }

            // Web requests get the Blade error page with countdown
            return response()->view('errors.429', [
                'retryAfter' => (int) $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
            ]);
        });
    })->create();
