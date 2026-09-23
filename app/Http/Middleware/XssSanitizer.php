<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class XssSanitizer
{
    /**
     * The attributes that should not be sanitized.
     *
     * @var array
     */
    protected $except = [
        'password',
        'password_confirmation',
        'current_password',
        '_token',
        '_method',
        'wysiwyg_*', // For WYSIWYG editors
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$this->shouldSanitize($request)) {
            return $next($request);
        }
        
        $this->sanitizeInputs($request);
        
        return $next($request);
    }
    
    /**
     * Determine if the request should be sanitized.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldSanitize(Request $request): bool
    {
        // Don't sanitize JSON requests
        if ($request->isJson()) {
            return false;
        }
        
        // Don't sanitize file uploads
        if ($request->hasFile('*')) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Sanitize the request inputs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function sanitizeInputs(Request $request): void
    {
        $inputs = $request->all();
        
        array_walk_recursive($inputs, function (&$input, $key) {
            if (is_string($input) && !$this->isExcepted($key)) {
                $input = $this->sanitize($input);
            }
        });
        
        $request->merge($inputs);
    }
    
    /**
     * Determine if the given key is excepted from sanitization.
     *
     * @param  string  $key
     * @return bool
     */
    protected function isExcepted(string $key): bool
    {
        foreach ($this->except as $excepted) {
            if ($excepted === $key) {
                return true;
            }
            
            if (str_ends_with($excepted, '*') && str_starts_with($key, substr($excepted, 0, -1))) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Sanitize the given input.
     *
     * @param  string  $input
     * @return string
     */
    protected function sanitize(string $input): string
    {
        // Remove null bytes
        $input = str_replace(chr(0), '', $input);
        
        // Remove HTML tags except for allowed ones
        $input = strip_tags($input, '<p><br><b><i><u><strong><em><ul><ol><li><a><h1><h2><h3><h4><h5><h6><blockquote><pre><code><img><table><thead><tbody><tr><th><td>');
        
        // Convert special characters to HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        
        return $input;
    }
}
