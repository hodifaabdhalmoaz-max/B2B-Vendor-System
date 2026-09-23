<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class MonitoringService
{
    /**
     * Get system health status
     *
     * @return array
     */
    public function getSystemHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'timestamp' => now()->toISOString(),
            'checks' => [],
        ];

        // Database health check
        $health['checks']['database'] = $this->checkDatabase();
        
        // Cache health check
        $health['checks']['cache'] = $this->checkCache();
        
        // Storage health check
        $health['checks']['storage'] = $this->checkStorage();
        
        // Queue health check
        $health['checks']['queue'] = $this->checkQueue();

        // Memory usage check
        $health['checks']['memory'] = $this->checkMemoryUsage();

        // Determine overall status
        $failedChecks = collect($health['checks'])->filter(function($check) {
            return $check['status'] !== 'healthy';
        });

        if ($failedChecks->count() > 0) {
            $health['status'] = $failedChecks->contains('status', 'critical') ? 'critical' : 'warning';
        }

        return $health;
    }

    /**
     * Check database connectivity and performance
     *
     * @return array
     */
    protected function checkDatabase(): array
    {
        try {
            $start = microtime(true);
            
            // Test basic connectivity
            DB::connection()->getPdo();
            
            // Test query performance
            $result = DB::select('SELECT 1 as test');
            
            $responseTime = (microtime(true) - $start) * 1000; // Convert to milliseconds

            return [
                'status' => $responseTime < 100 ? 'healthy' : ($responseTime < 500 ? 'warning' : 'critical'),
                'response_time_ms' => round($responseTime, 2),
                'message' => $responseTime < 100 ? 'Database is responsive' : 'Database response is slow',
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'response_time_ms' => null,
                'message' => 'Database connection failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache system health
     *
     * @return array
     */
    protected function checkCache(): array
    {
        try {
            $start = microtime(true);
            
            // Test cache write/read
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            Cache::put($testKey, $testValue, 60);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);
            
            $responseTime = (microtime(true) - $start) * 1000;

            if ($retrieved !== $testValue) {
                return [
                    'status' => 'critical',
                    'response_time_ms' => round($responseTime, 2),
                    'message' => 'Cache read/write test failed',
                ];
            }

            return [
                'status' => $responseTime < 50 ? 'healthy' : ($responseTime < 200 ? 'warning' : 'critical'),
                'response_time_ms' => round($responseTime, 2),
                'message' => $responseTime < 50 ? 'Cache is responsive' : 'Cache response is slow',
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'response_time_ms' => null,
                'message' => 'Cache system failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage system health
     *
     * @return array
     */
    protected function checkStorage(): array
    {
        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            
            $freePercentage = ($freeBytes / $totalBytes) * 100;
            
            $status = 'healthy';
            $message = 'Storage has sufficient space';
            
            if ($freePercentage < 10) {
                $status = 'critical';
                $message = 'Storage space is critically low';
            } elseif ($freePercentage < 20) {
                $status = 'warning';
                $message = 'Storage space is running low';
            }

            return [
                'status' => $status,
                'free_space_gb' => round($freeBytes / (1024 * 1024 * 1024), 2),
                'total_space_gb' => round($totalBytes / (1024 * 1024 * 1024), 2),
                'free_percentage' => round($freePercentage, 2),
                'message' => $message,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'message' => 'Storage check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check queue system health
     *
     * @return array
     */
    protected function checkQueue(): array
    {
        try {
            // Check if queue driver is working
            $queueDriver = config('queue.default');
            
            if ($queueDriver === 'sync') {
                return [
                    'status' => 'warning',
                    'driver' => $queueDriver,
                    'message' => 'Queue is using sync driver (not recommended for production)',
                ];
            }

            // For Redis queue, check Redis connectivity
            if ($queueDriver === 'redis') {
                try {
                    Redis::ping();
                    return [
                        'status' => 'healthy',
                        'driver' => $queueDriver,
                        'message' => 'Queue system is operational',
                    ];
                } catch (\Exception $e) {
                    return [
                        'status' => 'critical',
                        'driver' => $queueDriver,
                        'message' => 'Redis queue connection failed: ' . $e->getMessage(),
                    ];
                }
            }

            return [
                'status' => 'healthy',
                'driver' => $queueDriver,
                'message' => 'Queue system is operational',
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'critical',
                'message' => 'Queue check failed: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Check memory usage
     *
     * @return array
     */
    protected function checkMemoryUsage(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        $usagePercentage = ($memoryUsage / $memoryLimit) * 100;
        
        $status = 'healthy';
        $message = 'Memory usage is normal';
        
        if ($usagePercentage > 90) {
            $status = 'critical';
            $message = 'Memory usage is critically high';
        } elseif ($usagePercentage > 75) {
            $status = 'warning';
            $message = 'Memory usage is high';
        }

        return [
            'status' => $status,
            'usage_mb' => round($memoryUsage / (1024 * 1024), 2),
            'limit_mb' => round($memoryLimit / (1024 * 1024), 2),
            'usage_percentage' => round($usagePercentage, 2),
            'message' => $message,
        ];
    }

    /**
     * Parse memory limit string to bytes
     *
     * @param string $memoryLimit
     * @return int
     */
    protected function parseMemoryLimit(string $memoryLimit): int
    {
        $memoryLimit = trim($memoryLimit);
        $last = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Get performance metrics
     *
     * @return array
     */
    public function getPerformanceMetrics(): array
    {
        return [
            'timestamp' => now()->toISOString(),
            'response_times' => $this->getAverageResponseTimes(),
            'database_queries' => $this->getDatabaseMetrics(),
            'cache_metrics' => $this->getCacheMetrics(),
            'error_rates' => $this->getErrorRates(),
        ];
    }

    /**
     * Get average response times from logs
     *
     * @return array
     */
    protected function getAverageResponseTimes(): array
    {
        // This would typically read from application logs or APM tools
        // For now, return mock data
        return [
            'api_avg_ms' => 150,
            'web_avg_ms' => 300,
            'database_avg_ms' => 25,
        ];
    }

    /**
     * Get database performance metrics
     *
     * @return array
     */
    protected function getDatabaseMetrics(): array
    {
        try {
            // Get query count from Laravel's query log
            $queryCount = count(DB::getQueryLog());
            
            return [
                'active_connections' => DB::select('SHOW STATUS LIKE "Threads_connected"')[0]->Value ?? 'N/A',
                'queries_per_second' => $queryCount,
                'slow_queries' => 0, // Would need to implement slow query detection
            ];
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to get database metrics: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get cache performance metrics
     *
     * @return array
     */
    protected function getCacheMetrics(): array
    {
        try {
            $cacheService = app(CacheService::class);
            return $cacheService->getStatistics();
        } catch (\Exception $e) {
            return [
                'error' => 'Failed to get cache metrics: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get error rates from logs
     *
     * @return array
     */
    protected function getErrorRates(): array
    {
        // This would typically analyze log files
        // For now, return mock data
        return [
            'error_rate_percentage' => 0.1,
            'critical_errors_count' => 0,
            'warnings_count' => 2,
        ];
    }
}
