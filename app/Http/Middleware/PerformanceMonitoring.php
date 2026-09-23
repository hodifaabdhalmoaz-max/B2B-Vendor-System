<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitoring
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $queryCount = 0;
        $totalQueryTime = 0.0;

        DB::listen(function ($query) use (&$queryCount, &$totalQueryTime) {
            $queryCount++;
            $totalQueryTime += $query->time;
        });

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsage = $endMemory - $startMemory;
        // Add performance headers
        $response->headers->set('X-Response-Time', round($executionTime, 2).'ms');
        $response->headers->set('X-Memory-Usage', $this->formatBytes($memoryUsage));
        $response->headers->set('X-Query-Count', $queryCount);
        $response->headers->set('X-Query-Time', round($totalQueryTime, 2).'ms');

        // Log slow requests
        if ($executionTime > 1000) { // Slower than 1 second
            Log::channel('performance')->warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => round($executionTime, 2),
                'memory_usage' => $this->formatBytes($memoryUsage),
                'query_count' => $queryCount,
                'query_time_ms' => round($totalQueryTime, 2),
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
            ]);
        }

        // Log excessive database queries
        if ($queryCount > 50) {
            Log::channel('performance')->warning('Excessive database queries', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'query_count' => $queryCount,
                'query_time_ms' => round($totalQueryTime, 2),
                'execution_time_ms' => round($executionTime, 2),
                'user_id' => Auth::id(),
            ]);
        }

        // Log high memory usage
        if ($memoryUsage > 50 * 1024 * 1024) { // More than 50MB
            Log::channel('performance')->warning('High memory usage', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'memory_usage' => $this->formatBytes($memoryUsage),
                'execution_time_ms' => round($executionTime, 2),
                'user_id' => Auth::id(),
            ]);
        }

        return $response;
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
