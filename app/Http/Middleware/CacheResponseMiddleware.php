<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CacheResponseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $ttl = 60): Response
    {
        // Don't cache if:
        // 1. Request is not a GET request
        // 2. Request is from authenticated user
        // 3. Request has query parameters that should not be cached
        if ($request->method() !== 'GET' || 
            $request->user() || 
            $this->shouldSkipCache($request)) {
            return $next($request);
        }

        // Generate cache key based on full URL
        $cacheKey = 'response_' . md5($request->fullUrl());

        // Return cached response if exists
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Get response
        $response = $next($request);

        // Cache response if it's successful
        if ($response->isSuccessful()) {
            Cache::put($cacheKey, $response, $ttl);
        }

        return $response;
    }

    /**
     * Determine if the request has query parameters that should not be cached.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSkipCache(Request $request): bool
    {
        // Skip caching for specific query parameters
        $noCacheParams = ['token', 'refresh', 'nocache'];

        foreach ($noCacheParams as $param) {
            if ($request->has($param)) {
                return true;
            }
        }

        return false;
    }
}
