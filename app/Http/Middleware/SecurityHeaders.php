<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Prevent clickjacking attacks
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');

        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        // Enable XSS protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');

        // Enforce HTTPS (only in production)
        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        // Content Security Policy
        $csp = $this->buildContentSecurityPolicy();
        $response->headers->set('Content-Security-Policy', $csp);

        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        // Permissions Policy (formerly Feature Policy)
        $permissionsPolicy = $this->buildPermissionsPolicy();
        $response->headers->set('Permissions-Policy', $permissionsPolicy);

        // Remove server information
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');

        return $response;
    }

    /**
     * Build Content Security Policy header.
     *
     * @return string
     */
    protected function buildContentSecurityPolicy(): string
    {
        // Use associative array to prevent duplicate directives
        $policies = [
            'default-src' => "'self'",
            'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://code.jquery.com https://stackpath.bootstrapcdn.com https://www.googletagmanager.com https://www.google-analytics.com https://connect.facebook.net https://www.facebook.com",
            'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com https://stackpath.bootstrapcdn.com",
            'font-src' => "'self' data: blob: https://fonts.gstatic.com https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            'img-src' => "'self' data: https: blob: *.googleapis.com *.gstatic.com",
            'connect-src' => "'self' https://api.whatsapp.com https://wa.me https://www.google-analytics.com https://stats.g.doubleclick.net",
            'frame-src' => "'self' https://www.youtube.com https://www.google.com https://www.facebook.com",
            'object-src' => "'none'",
            'base-uri' => "'self'",
            'form-action' => "'self'",
            'frame-ancestors' => "'self'",
            'manifest-src' => "'self'",
            'worker-src' => "'self' blob:",
        ];

        // Add upgrade-insecure-requests only in production
        if (app()->environment('production')) {
            $policies['upgrade-insecure-requests'] = '';
        }

        // In development, allow more sources (overrides instead of duplicating)
        if (app()->environment('local', 'development')) {
            $policies['script-src'] = "'self' 'unsafe-inline' 'unsafe-eval' *";
            $policies['style-src'] = "'self' 'unsafe-inline' *";
            $policies['connect-src'] = "'self' *";
            $policies['font-src'] = "'self' data: blob: *";
        }

        // Build the CSP string
        $cspParts = [];
        foreach ($policies as $directive => $value) {
            $cspParts[] = trim("{$directive} {$value}");
        }

        return implode('; ', $cspParts);
    }

    /**
     * Build Permissions Policy header.
     *
     * @return string
     */
    protected function buildPermissionsPolicy(): string
    {
        $policies = [
            'camera=()',
            'microphone=()',
            'geolocation=(self)',
            'payment=(self)',
            'usb=()',
            'magnetometer=()',
            'accelerometer=()',
            'gyroscope=()',
            'fullscreen=(self)',
            'picture-in-picture=()',
        ];

        return implode(', ', $policies);
    }
}
