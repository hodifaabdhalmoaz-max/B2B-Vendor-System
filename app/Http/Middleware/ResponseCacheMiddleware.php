<?php

// app/Http/Middleware/ResponseCacheMiddleware.php
// Proper page-level caching for static/semi-static pages.
// Caches rendered HTML content (not Response objects) to avoid serialization issues.
// Replaces the old CacheResponseMiddleware.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

final class ResponseCacheMiddleware
{
    /**
     * Routes that should never be cached regardless of settings.
     */
    private const NEVER_CACHE_ROUTES = [
        'cart.*',
        'checkout',
        'cart.checkout',
        'cart.place.an.order',
        'cart.order.confirmation',
        'user.*',
        'admin.*',
        'login',
        'register',
        'logout',
        'password.*',
    ];

    /**
     * Handle an incoming request with response caching.
     *
     * @param  int  $ttl  Cache duration in seconds (default 300 = 5 minutes)
     */
    public function handle(Request $request, Closure $next, int $ttl = 300): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }

        // Never cache for authenticated users (personalized content)
        if ($request->user()) {
            return $next($request);
        }

        // Skip cache for routes that should never be cached
        if ($this->shouldSkipCache($request)) {
            return $next($request);
        }

        // Build a cache key from the full URL (includes query params for paginated/filtered pages)
        $cacheKey = 'page_cache:' . sha1($request->fullUrl());

        // Check if we have a cached version
        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            // Reconstruct the response from cached HTML content
            return response($cached['content'], $cached['status'])
                ->withHeaders(array_merge($cached['headers'], [
                    'X-Cache' => 'HIT',
                    'X-Cache-Expires' => $cached['expires_at'],
                ]));
        }

        // No cache — process the request normally
        $response = $next($request);

        // Only cache successful HTML responses
        if ($response->isSuccessful() && $this->isHtmlResponse($response)) {
            $expiresAt = now()->addSeconds($ttl)->toIso8601String();

            Cache::put($cacheKey, [
                'content' => $response->getContent(),
                'status' => $response->getStatusCode(),
                'headers' => [
                    'Content-Type' => $response->headers->get('Content-Type', 'text/html'),
                ],
                'expires_at' => $expiresAt,
                'cached_at' => now()->toIso8601String(),
            ], $ttl);

            $response->headers->set('X-Cache', 'MISS');
        }

        return $response;
    }

    /**
     * Check if the request should skip caching.
     */
    private function shouldSkipCache(Request $request): bool
    {
        // Skip if explicit no-cache params are present
        $noCacheParams = ['token', 'refresh', 'nocache', '_'];
        foreach ($noCacheParams as $param) {
            if ($request->has($param)) {
                return true;
            }
        }

        // Skip routes that should never be cached
        foreach (self::NEVER_CACHE_ROUTES as $pattern) {
            if ($request->routeIs($pattern)) {
                return true;
            }
        }

        // Skip if the request has a CSRF token (form submission)
        if ($request->hasHeader('X-CSRF-TOKEN') || $request->has('_token')) {
            return true;
        }

        return false;
    }

    /**
     * Check if the response contains HTML content.
     */
    private function isHtmlResponse(Response $response): bool
    {
        $contentType = $response->headers->get('Content-Type', '');
        return str_contains($contentType, 'text/html');
    }
}
