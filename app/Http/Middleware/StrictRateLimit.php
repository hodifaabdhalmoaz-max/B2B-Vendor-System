<?php

// app/Http/Middleware/StrictRateLimit.php
// Improved version that returns proper HTML for web requests
// and JSON for API requests. Kept for backward compatibility.
// New routes should use SmartThrottle instead.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class StrictRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 10, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);
        
        // Get current attempts
        $attempts = Cache::get($key, 0);
        
        // Check if limit exceeded
        if ($attempts >= $maxAttempts) {
            // Log suspicious activity
            Log::warning('Rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'attempts' => $attempts,
                'max_attempts' => $maxAttempts,
            ]);
            
            $retryAfter = $decayMinutes * 60;

            // Return JSON for API/AJAX requests
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'تم تجاوز عدد الطلبات المسموح. حاول مرة أخرى لاحقاً.',
                    'retry_after' => $retryAfter,
                ], 429, [
                    'Retry-After' => $retryAfter,
                    'X-RateLimit-Limit' => $maxAttempts,
                    'X-RateLimit-Remaining' => 0,
                ]);
            }

            // Return the Blade 429 error page for web requests
            return response()->view('errors.429', [
                'retryAfter' => $retryAfter,
            ], 429, [
                'Retry-After' => $retryAfter,
            ]);
        }
        
        // Increment attempts
        Cache::put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        
        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', max(0, $maxAttempts - $attempts - 1));
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);
        
        return $response;
    }
    
    /**
     * Resolve request signature.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $userId = $request->user()?->id ?? 'guest';
        $ip = $request->ip();
        $route = $request->route()?->getName() ?? $request->path();
        
        return 'rate_limit:' . sha1($userId . '|' . $ip . '|' . $route);
    }
}
