<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class SecureSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Regenerate session ID periodically for security
        $this->regenerateSessionIfNeeded($request);
        
        // Check for session hijacking
        $this->checkSessionSecurity($request);
        
        // Set secure session configuration
        $this->configureSecureSession($request);
        
        $response = $next($request);
        
        // Log session activity for audit
        $this->logSessionActivity($request);
        
        return $response;
    }
    
    /**
     * Regenerate session ID if needed.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function regenerateSessionIfNeeded(Request $request): void
    {
        $lastRegeneration = Session::get('last_regeneration', 0);
        $regenerateInterval = config('security.session.regenerate_frequency', 15) * 60; // Convert to seconds
        
        if (time() - $lastRegeneration > $regenerateInterval) {
            Session::regenerate(true);
            Session::put('last_regeneration', time());
            
            Log::info('Session regenerated for security', [
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }
    }
    
    /**
     * Check session security indicators.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function checkSessionSecurity(Request $request): void
    {
        $sessionUserAgent = Session::get('user_agent');
        $sessionIp = Session::get('ip_address');
        
        // First time setting session fingerprint
        if (!$sessionUserAgent || !$sessionIp) {
            Session::put('user_agent', $request->userAgent());
            Session::put('ip_address', $request->ip());
            return;
        }
        
        // Check for potential session hijacking
        if ($sessionUserAgent !== $request->userAgent()) {
            Log::warning('Potential session hijacking detected - User Agent mismatch', [
                'user_id' => $request->user()?->id,
                'session_ua' => $sessionUserAgent,
                'request_ua' => $request->userAgent(),
                'ip' => $request->ip(),
            ]);
            
            // Invalidate session
            Session::invalidate();
            Session::regenerateToken();
        }
        
        // Check for IP address changes (less strict, just log)
        if ($sessionIp !== $request->ip()) {
            Log::info('IP address changed during session', [
                'user_id' => $request->user()?->id,
                'old_ip' => $sessionIp,
                'new_ip' => $request->ip(),
            ]);
            
            // Update IP in session
            Session::put('ip_address', $request->ip());
        }
    }
    
    /**
     * Configure secure session settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function configureSecureSession(Request $request): void
    {
        // Set session configuration based on environment
        if (app()->environment('production')) {
            config([
                'session.secure' => true,
                'session.http_only' => true,
                'session.same_site' => 'strict',
            ]);
        }
        
        // Set session timeout for inactive users
        $lastActivity = Session::get('last_activity', time());
        $timeout = config('security.session.lifetime', 120) * 60; // Convert to seconds
        
        if (time() - $lastActivity > $timeout) {
            Session::invalidate();
            Session::regenerateToken();
            
            Log::info('Session expired due to inactivity', [
                'user_id' => $request->user()?->id,
                'last_activity' => date('Y-m-d H:i:s', $lastActivity),
                'ip' => $request->ip(),
            ]);
        } else {
            Session::put('last_activity', time());
        }
    }
    
    /**
     * Log session activity for audit purposes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function logSessionActivity(Request $request): void
    {
        // Only log sensitive routes
        $sensitiveRoutes = [
            'login', 'logout', 'register', 'password.reset',
            'admin.*', 'cart.checkout', 'cart.place.an.order'
        ];
        
        $routeName = $request->route()?->getName();
        
        if ($routeName && $this->isSensitiveRoute($routeName, $sensitiveRoutes)) {
            Log::info('Session activity on sensitive route', [
                'route' => $routeName,
                'user_id' => $request->user()?->id,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => Session::getId(),
            ]);
        }
    }
    
    /**
     * Check if route is sensitive.
     *
     * @param  string  $routeName
     * @param  array  $sensitiveRoutes
     * @return bool
     */
    protected function isSensitiveRoute(string $routeName, array $sensitiveRoutes): bool
    {
        foreach ($sensitiveRoutes as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }
        
        return false;
    }
}
