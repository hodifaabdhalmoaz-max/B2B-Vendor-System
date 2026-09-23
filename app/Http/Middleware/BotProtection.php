<?php

// app/Http/Middleware/BotProtection.php
// Detects and blocks suspicious bots, manages dynamic IP blacklist,
// adds security response headers, and integrates with the throttle system.

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

final class BotProtection
{
    /**
     * Cache key prefixes.
     */
    private const BLACKLIST_PREFIX = 'bot_protection:blacklist:';
    private const REQUEST_COUNT_PREFIX = 'bot_protection:count:';
    private const FINGERPRINT_PREFIX = 'bot_protection:fingerprint:';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip if bot protection is disabled
        if (!config('throttle.bot_protection.enabled', true)) {
            return $next($request);
        }

        $ip = $request->ip();
        $userAgent = strtolower($request->userAgent() ?? '');

        // Step 1: Check whitelist — always allow these IPs
        if ($this->isWhitelisted($ip)) {
            return $this->addSecurityHeaders($next($request));
        }

        // Step 2: Check if IP is currently blacklisted
        if ($this->isBlacklisted($ip)) {
            Log::info('BotProtection: Blocked blacklisted IP', [
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            return $this->blockedResponse($request);
        }

        // Step 3: Check for blocked user agents
        if ($this->isBlockedUserAgent($userAgent)) {
            $this->addToBlacklist($ip, reason: 'blocked_user_agent');

            Log::warning('BotProtection: Blocked malicious user agent', [
                'ip' => $ip,
                'user_agent' => $userAgent,
            ]);

            return $this->blockedResponse($request);
        }

        // Step 4: Check for missing or empty user agent (strong bot indicator)
        if (empty($userAgent)) {
            $this->addToBlacklist($ip, reason: 'empty_user_agent');

            Log::warning('BotProtection: Blocked empty user agent', [
                'ip' => $ip,
            ]);

            return $this->blockedResponse($request);
        }

        // Step 5: Track request frequency for this IP
        $requestCount = $this->incrementRequestCount($ip);
        $threshold = config('throttle.bot_protection.suspicious_threshold', 100);

        // Step 6: If request count exceeds threshold, blacklist the IP
        if ($requestCount > $threshold) {
            $this->addToBlacklist($ip, reason: 'excessive_requests');

            Log::warning('BotProtection: IP blacklisted for excessive requests', [
                'ip' => $ip,
                'request_count' => $requestCount,
                'threshold' => $threshold,
                'user_agent' => $userAgent,
                'url' => $request->fullUrl(),
            ]);

            return $this->blockedResponse($request);
        }

        // Step 7: Check for suspicious patterns (rapid identical requests)
        if ($this->hasSuspiciousPattern($request)) {
            Log::warning('BotProtection: Suspicious request pattern detected', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'url' => $request->fullUrl(),
            ]);

            // Don't blacklist yet — just warn in logs for now
            // The SmartThrottle middleware will handle the actual blocking
        }

        // Step 8: Identify known good bots and skip aggressive throttling for them
        $isGoodBot = $this->isGoodBot($userAgent);
        if ($isGoodBot) {
            // Mark request as from a good bot so SmartThrottle can adjust
            $request->attributes->set('is_good_bot', true);
        }

        // Process the request and add security headers
        $response = $next($request);

        return $this->addSecurityHeaders($response);
    }

    /**
     * Check if an IP is whitelisted.
     */
    private function isWhitelisted(string $ip): bool
    {
        $whitelisted = config('throttle.bot_protection.whitelisted_ips', []);
        return in_array($ip, $whitelisted, strict: true);
    }

    /**
     * Check if an IP is currently blacklisted.
     */
    private function isBlacklisted(string $ip): bool
    {
        return Cache::has(self::BLACKLIST_PREFIX . $ip);
    }

    /**
     * Add an IP to the dynamic blacklist with automatic expiry.
     */
    private function addToBlacklist(string $ip, string $reason = 'unknown'): void
    {
        $duration = config('throttle.bot_protection.block_duration', 15);

        Cache::put(
            key: self::BLACKLIST_PREFIX . $ip,
            value: [
                'reason' => $reason,
                'blocked_at' => now()->toIso8601String(),
                'expires_at' => now()->addMinutes($duration)->toIso8601String(),
            ],
            ttl: now()->addMinutes($duration),
        );
    }

    /**
     * Check if the user agent matches any blocked patterns.
     */
    private function isBlockedUserAgent(string $userAgent): bool
    {
        $blockedAgents = config('throttle.bot_protection.blocked_user_agents', []);

        foreach ($blockedAgents as $pattern) {
            if (str_contains($userAgent, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user agent is a known good bot (Google, Bing, etc.).
     */
    private function isGoodBot(string $userAgent): bool
    {
        $goodBots = config('throttle.bot_protection.good_bots', []);

        foreach ($goodBots as $pattern) {
            if (str_contains($userAgent, strtolower($pattern))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Increment and return the request count for an IP within the time window.
     */
    private function incrementRequestCount(string $ip): int
    {
        $key = self::REQUEST_COUNT_PREFIX . $ip;
        $window = config('throttle.bot_protection.window_minutes', 1);

        $count = (int) Cache::get($key, 0);
        Cache::put($key, $count + 1, now()->addMinutes($window));

        return $count + 1;
    }

    /**
     * Detect suspicious patterns: rapid identical requests from same IP.
     * Uses a fingerprint of the last N request paths to detect repetition.
     */
    private function hasSuspiciousPattern(Request $request): bool
    {
        $ip = $request->ip();
        $key = self::FINGERPRINT_PREFIX . $ip;
        $path = $request->path();

        // Get recent request paths for this IP
        $recentPaths = Cache::get($key, []);
        $recentPaths[] = $path;

        // Keep only the last 20 paths
        if (count($recentPaths) > 20) {
            $recentPaths = array_slice($recentPaths, -20);
        }

        Cache::put($key, $recentPaths, now()->addMinutes(2));

        // If all 20 recent paths are identical → suspicious bot behavior
        if (count($recentPaths) >= 20) {
            $uniquePaths = array_unique($recentPaths);
            if (count($uniquePaths) <= 2) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build a blocked response.
     */
    private function blockedResponse(Request $request): Response
    {
        $retryAfter = config('throttle.bot_protection.block_duration', 15) * 60;

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'تم حظر الوصول مؤقتاً بسبب نشاط مشبوه.',
                'error' => 'ip_blocked',
                'retry_after' => $retryAfter,
            ], 403, [
                'Retry-After' => $retryAfter,
            ]);
        }

        return response()->view('errors.429', [
            'retryAfter' => $retryAfter,
        ], 403, [
            'Retry-After' => $retryAfter,
        ]);
    }

    /**
     * Add security response headers to mitigate basic DDoS and scraping.
     */
    private function addSecurityHeaders(Response $response): Response
    {
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Prevent embedding in frames from other origins
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Hide server software information
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        // NOTE: Do NOT override Cache-Control here — it breaks CSRF tokens
        // and session cookies. Let Laravel and .htaccess handle caching.

        return $response;
    }
}
