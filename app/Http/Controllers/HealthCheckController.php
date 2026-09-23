<?php

namespace App\Http\Controllers;

use App\Services\MonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HealthCheckController extends Controller
{
    protected $monitoringService;

    public function __construct(MonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Get system health status
     */
    public function health(): JsonResponse
    {
        $health = $this->monitoringService->getSystemHealth();

        $statusCode = match($health['status']) {
            'healthy' => 200,
            'warning' => 200,
            'critical' => 503,
            default => 500,
        };

        return response()->json($health, $statusCode);
    }

    /**
     * Get performance metrics
     */
    public function metrics(): JsonResponse
    {
        $metrics = $this->monitoringService->getPerformanceMetrics();

        return response()->json($metrics);
    }

    /**
     * Simple ping endpoint
     */
    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'message' => 'Service is running',
        ]);
    }

    /**
     * Get detailed system information (admin only)
     */
    public function info(Request $request): JsonResponse
    {
        // Check if user is admin
        if (!$request->user() || $request->user()->utype !== 'ADM') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $info = [
            'app' => [
                'name' => config('app.name'),
                'env' => config('app.env'),
                'debug' => config('app.debug'),
                'url' => config('app.url'),
                'timezone' => config('app.timezone'),
                'locale' => config('app.locale'),
            ],
            'laravel' => [
                'version' => app()->version(),
                'php_version' => PHP_VERSION,
            ],
            'database' => [
                'driver' => config('database.default'),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
            ],
            'cache' => [
                'driver' => config('cache.default'),
                'prefix' => config('cache.prefix'),
            ],
            'queue' => [
                'driver' => config('queue.default'),
            ],
            'mail' => [
                'driver' => config('mail.default'),
                'from' => config('mail.from'),
            ],
            'server' => [
                'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'php_sapi' => PHP_SAPI,
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
        ];

        return response()->json($info);
    }
}
