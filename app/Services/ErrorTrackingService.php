<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ErrorTrackingService
{
    /**
     * Track an error with detailed context
     *
     * @param Throwable $exception
     * @param array $context
     * @return void
     */
    public function trackError(Throwable $exception, array $context = []): void
    {
        $errorData = $this->buildErrorData($exception, $context);
        
        // Log to Laravel log
        Log::error('Application Error', $errorData);
        
        // Store in database for analysis
        $this->storeErrorInDatabase($errorData);
        
        // Update error metrics
        $this->updateErrorMetrics($errorData);
    }

    /**
     * Build comprehensive error data
     *
     * @param Throwable $exception
     * @param array $context
     * @return array
     */
    protected function buildErrorData(Throwable $exception, array $context = []): array
    {
        return [
            'error_id' => uniqid('err_', true),
            'timestamp' => now()->toISOString(),
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $this->sanitizeStackTrace($exception->getTraceAsString()),
            ],
            'request' => $this->getRequestData(),
            'user' => $this->getUserData(),
            'environment' => [
                'app_env' => config('app.env'),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
            ],
            'context' => $context,
            'severity' => $this->determineSeverity($exception),
        ];
    }

    /**
     * Get current request data
     *
     * @return array
     */
    protected function getRequestData(): array
    {
        if (!app()->runningInConsole() && request()) {
            return [
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'headers' => $this->sanitizeHeaders(request()->headers->all()),
                'input' => $this->sanitizeInput(request()->all()),
            ];
        }

        return [
            'type' => 'console',
            'command' => $_SERVER['argv'][1] ?? 'unknown',
        ];
    }

    /**
     * Get current user data
     *
     * @return array|null
     */
    protected function getUserData(): ?array
    {
        if (auth()->check()) {
            return [
                'id' => auth()->id(),
                'email' => auth()->user()->email,
                'type' => auth()->user()->utype,
            ];
        }

        return null;
    }

    /**
     * Sanitize stack trace to remove sensitive information
     *
     * @param string $trace
     * @return string
     */
    protected function sanitizeStackTrace(string $trace): string
    {
        // Remove sensitive patterns
        $patterns = [
            '/password[\'"]?\s*=>\s*[\'"][^\'"]*[\'"]/i',
            '/token[\'"]?\s*=>\s*[\'"][^\'"]*[\'"]/i',
            '/secret[\'"]?\s*=>\s*[\'"][^\'"]*[\'"]/i',
            '/key[\'"]?\s*=>\s*[\'"][^\'"]*[\'"]/i',
        ];

        $replacements = [
            'password => [REDACTED]',
            'token => [REDACTED]',
            'secret => [REDACTED]',
            'key => [REDACTED]',
        ];

        return preg_replace($patterns, $replacements, $trace);
    }

    /**
     * Sanitize request headers
     *
     * @param array $headers
     * @return array
     */
    protected function sanitizeHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[REDACTED]'];
            }
        }

        return $headers;
    }

    /**
     * Sanitize request input
     *
     * @param array $input
     * @return array
     */
    protected function sanitizeInput(array $input): array
    {
        $sensitiveFields = ['password', 'password_confirmation', 'token', 'secret', 'key'];
        
        foreach ($sensitiveFields as $field) {
            if (isset($input[$field])) {
                $input[$field] = '[REDACTED]';
            }
        }

        return $input;
    }

    /**
     * Determine error severity
     *
     * @param Throwable $exception
     * @return string
     */
    protected function determineSeverity(Throwable $exception): string
    {
        $criticalExceptions = [
            'Illuminate\Database\QueryException',
            'PDOException',
            'Illuminate\Contracts\Filesystem\FileNotFoundException',
        ];

        $warningExceptions = [
            'Illuminate\Validation\ValidationException',
            'Symfony\Component\HttpKernel\Exception\NotFoundHttpException',
        ];

        $exceptionClass = get_class($exception);

        if (in_array($exceptionClass, $criticalExceptions)) {
            return 'critical';
        }

        if (in_array($exceptionClass, $warningExceptions)) {
            return 'warning';
        }

        return 'error';
    }

    /**
     * Store error in database for analysis
     *
     * @param array $errorData
     * @return void
     */
    protected function storeErrorInDatabase(array $errorData): void
    {
        try {
            DB::table('error_logs')->insert([
                'error_id' => $errorData['error_id'],
                'severity' => $errorData['severity'],
                'exception_class' => $errorData['exception']['class'],
                'message' => $errorData['exception']['message'],
                'file' => $errorData['exception']['file'],
                'line' => $errorData['exception']['line'],
                'url' => $errorData['request']['url'] ?? null,
                'method' => $errorData['request']['method'] ?? null,
                'user_id' => $errorData['user']['id'] ?? null,
                'ip_address' => $errorData['request']['ip'] ?? null,
                'user_agent' => $errorData['request']['user_agent'] ?? null,
                'context' => json_encode($errorData['context']),
                'stack_trace' => $errorData['exception']['trace'],
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // If database storage fails, just log it
            Log::warning('Failed to store error in database', [
                'error' => $e->getMessage(),
                'original_error_id' => $errorData['error_id'],
            ]);
        }
    }

    /**
     * Update error metrics in cache
     *
     * @param array $errorData
     * @return void
     */
    protected function updateErrorMetrics(array $errorData): void
    {
        try {
            $today = now()->format('Y-m-d');
            $hour = now()->format('Y-m-d-H');
            
            // Daily error count
            Cache::increment("errors:daily:{$today}");
            
            // Hourly error count
            Cache::increment("errors:hourly:{$hour}");
            
            // Error count by severity
            Cache::increment("errors:severity:{$errorData['severity']}:{$today}");
            
            // Error count by exception class
            $exceptionClass = str_replace('\\', '_', $errorData['exception']['class']);
            Cache::increment("errors:class:{$exceptionClass}:{$today}");
            
        } catch (\Exception $e) {
            Log::warning('Failed to update error metrics', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get error statistics
     *
     * @param int $days
     * @return array
     */
    public function getErrorStatistics(int $days = 7): array
    {
        try {
            $stats = [
                'daily_counts' => [],
                'severity_breakdown' => [],
                'top_exceptions' => [],
                'total_errors' => 0,
            ];

            // Get daily error counts
            for ($i = 0; $i < $days; $i++) {
                $date = now()->subDays($i)->format('Y-m-d');
                $count = Cache::get("errors:daily:{$date}", 0);
                $stats['daily_counts'][$date] = $count;
                $stats['total_errors'] += $count;
            }

            // Get severity breakdown for today
            $today = now()->format('Y-m-d');
            $stats['severity_breakdown'] = [
                'critical' => Cache::get("errors:severity:critical:{$today}", 0),
                'error' => Cache::get("errors:severity:error:{$today}", 0),
                'warning' => Cache::get("errors:severity:warning:{$today}", 0),
            ];

            // Get top exceptions from database
            $stats['top_exceptions'] = DB::table('error_logs')
                ->select('exception_class', DB::raw('COUNT(*) as count'))
                ->where('created_at', '>=', now()->subDays($days))
                ->groupBy('exception_class')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get()
                ->toArray();

            return $stats;

        } catch (\Exception $e) {
            Log::error('Failed to get error statistics', [
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => 'Failed to retrieve error statistics',
            ];
        }
    }

    /**
     * Clear old error logs
     *
     * @param int $days
     * @return int
     */
    public function clearOldErrors(int $days = 30): int
    {
        try {
            return DB::table('error_logs')
                ->where('created_at', '<', now()->subDays($days))
                ->delete();
        } catch (\Exception $e) {
            Log::error('Failed to clear old error logs', [
                'error' => $e->getMessage(),
            ]);

            return 0;
        }
    }
}
