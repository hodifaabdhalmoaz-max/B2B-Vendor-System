<?php

// app/Http/Middleware/SmartThrottle.php
// Intelligent rate-limiting middleware with:
//   - Per-IP, per-route-type, per-user-role differentiation
//   - Request statistics stored in cache
//   - Log warnings when thresholds are exceeded
//   - Exponential backoff for repeat offenders

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class SmartThrottle
{
    /**
     * Handle an incoming request with intelligent rate limiting.
     *
     * @param  string  $routeType  One of: public, cart, checkout, search, wishlist, user, admin
     */
    public function handle(Request $request, Closure $next, string $routeType = 'public'): Response
    {
        $ip = $request->ip();

        // Step 1: Check if this IP is currently in exponential backoff (blocked)
        $backoffKey = "throttle:backoff:{$ip}";
        if (Cache::has($backoffKey)) {
            $blockedUntil = Cache::get($backoffKey);
            $retryAfter = max(1, $blockedUntil - now()->timestamp);

            Log::warning('SmartThrottle: Blocked IP attempted access during backoff', [
                'ip' => $ip,
                'route_type' => $routeType,
                'blocked_until' => date('Y-m-d H:i:s', $blockedUntil),
                'url' => $request->fullUrl(),
            ]);

            return $this->buildResponse($request, $retryAfter);
        }

        // Step 2: Resolve the rate limit for this request
        $maxAttempts = $this->resolveMaxAttempts($request, $routeType);
        $decayMinutes = $this->resolveDecayMinutes($routeType);

        // Step 3: Build the cache key (IP + route type)
        $cacheKey = $this->buildCacheKey($request, $routeType);

        // Step 4: Get current request count
        $currentAttempts = (int) Cache::get($cacheKey, 0);

        // Step 5: Check if limit is exceeded
        if ($currentAttempts >= $maxAttempts) {
            // Track this as an offense for exponential backoff
            $this->recordOffense($ip);

            // Log the threshold breach
            Log::warning('SmartThrottle: Rate limit exceeded', [
                'ip' => $ip,
                'user_id' => $request->user()?->id,
                'route_type' => $routeType,
                'attempts' => $currentAttempts,
                'max_attempts' => $maxAttempts,
                'url' => $request->fullUrl(),
                'user_agent' => $request->userAgent(),
            ]);

            // Apply exponential backoff if this is a repeat offender
            $this->applyBackoffIfNeeded($ip);

            $retryAfter = $decayMinutes * 60;

            return $this->buildResponse($request, $retryAfter);
        }

        // Step 6: Increment the counter
        Cache::put($cacheKey, $currentAttempts + 1, now()->addMinutes($decayMinutes));

        // Step 7: Track request statistics (sampled — every 10th request to reduce overhead)
        if ($currentAttempts % 10 === 0) {
            $this->trackStatistics($ip, $routeType);
        }

        // Step 8: Process the request
        $response = $next($request);

        // Step 9: Add rate limit headers to the response
        $remaining = max(0, $maxAttempts - $currentAttempts - 1);
        $response->headers->set('X-RateLimit-Limit', (string) $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', (string) $remaining);
        $response->headers->set('X-RateLimit-Reset', (string) now()->addMinutes($decayMinutes)->timestamp);

        return $response;
    }

    /**
     * Resolve the maximum number of attempts based on route type, user role, and config.
     */
    private function resolveMaxAttempts(Request $request, string $routeType): int
    {
        // Read from centralized config
        $configKey = "throttle.web.{$routeType}";
        $config = config($configKey);

        // Fallback if route type not found in config
        $baseLimit = $config['max_attempts'] ?? 120;

        $user = $request->user();

        // No user — return base limit for guests
        if (!$user) {
            return $baseLimit;
        }

        // Admin users get the admin multiplier
        if (method_exists($user, 'isAdmin') && $user->isAdmin()) {
            return (int) ($baseLimit * config('throttle.admin_multiplier', 5.0));
        }

        // Authenticated regular users get the auth multiplier
        return (int) ($baseLimit * config('throttle.auth_multiplier', 2.0));
    }

    /**
     * Resolve the decay window in minutes for the route type.
     */
    private function resolveDecayMinutes(string $routeType): int
    {
        $configKey = "throttle.web.{$routeType}";
        return (int) (config("{$configKey}.decay_minutes") ?? 1);
    }

    /**
     * Build a unique cache key for this IP + route type combination.
     */
    private function buildCacheKey(Request $request, string $routeType): string
    {
        $identifier = $request->user()?->id ?: $request->ip();
        return "smart_throttle:{$routeType}:{$identifier}";
    }

    /**
     * Record an offense for exponential backoff tracking.
     */
    private function recordOffense(string $ip): void
    {
        $offenseKey = "throttle:offenses:{$ip}";
        $offenses = (int) Cache::get($offenseKey, 0);
        // Track offenses for 1 hour
        Cache::put($offenseKey, $offenses + 1, now()->addHour());
    }

    /**
     * Apply exponential backoff if the IP has exceeded the offense threshold.
     */
    private function applyBackoffIfNeeded(string $ip): void
    {
        if (!config('throttle.backoff.enabled', true)) {
            return;
        }

        $offenseKey = "throttle:offenses:{$ip}";
        $offenses = (int) Cache::get($offenseKey, 0);
        $threshold = config('throttle.backoff.offense_threshold', 3);

        // Only apply backoff after the threshold is reached
        if ($offenses < $threshold) {
            return;
        }

        // Calculate block duration: base * multiplier^(offenses - threshold)
        $base = config('throttle.backoff.base_minutes', 1);
        $multiplier = config('throttle.backoff.multiplier', 2);
        $maxMinutes = config('throttle.backoff.max_minutes', 60);

        $exponent = $offenses - $threshold;
        $blockMinutes = min($base * ($multiplier ** $exponent), $maxMinutes);

        $backoffKey = "throttle:backoff:{$ip}";
        $blockedUntil = now()->addMinutes($blockMinutes)->timestamp;
        Cache::put($backoffKey, $blockedUntil, now()->addMinutes($blockMinutes));

        Log::warning('SmartThrottle: Exponential backoff applied', [
            'ip' => $ip,
            'offenses' => $offenses,
            'block_minutes' => $blockMinutes,
            'blocked_until' => date('Y-m-d H:i:s', $blockedUntil),
        ]);
    }

    /**
     * Track request statistics in cache for monitoring.
     */
    private function trackStatistics(string $ip, string $routeType): void
    {
        $statsKey = 'throttle:stats:' . now()->format('Y-m-d:H');
        $stats = Cache::get($statsKey, [
            'total_requests' => 0,
            'unique_ips' => [],
            'by_route_type' => [],
        ]);

        $stats['total_requests'] += 10; // We sample every 10th request
        $stats['unique_ips'][$ip] = ($stats['unique_ips'][$ip] ?? 0) + 10;
        $stats['by_route_type'][$routeType] = ($stats['by_route_type'][$routeType] ?? 0) + 10;

        // Keep stats for 24 hours
        Cache::put($statsKey, $stats, now()->addHours(24));
    }

    /**
     * Build the appropriate 429 response based on request type.
     */
    private function buildResponse(Request $request, int $retryAfter): Response
    {
        // API / AJAX requests get a JSON response
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'تم تجاوز عدد الطلبات المسموح. حاول مرة أخرى لاحقاً.',
                'error' => 'rate_limit_exceeded',
                'retry_after' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
                'X-RateLimit-Limit' => 0,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        // Web requests get the Blade 429 error page
        return response()->view('errors.429', [
            'retryAfter' => $retryAfter,
        ], 429, [
            'Retry-After' => $retryAfter,
        ]);
    }
}
