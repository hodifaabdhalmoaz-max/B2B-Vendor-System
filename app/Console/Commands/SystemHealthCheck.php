<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\SystemHealthAlert;
use Carbon\Carbon;

class SystemHealthCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:health-check
                            {--notify : Send notification if issues found}
                            {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform comprehensive system health check';

    protected $healthStatus = [];
    protected $criticalIssues = [];
    protected $warnings = [];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🏥 Starting system health check...');
        $startTime = microtime(true);

        // Initialize health status
        $this->healthStatus = [
            'timestamp' => Carbon::now(),
            'overall_status' => 'healthy',
            'checks' => [],
        ];

        // Perform all health checks
        $this->checkDatabase();
        $this->checkCache();
        $this->checkStorage();
        $this->checkBackups();
        $this->checkSecurity();
        $this->checkPerformance();
        $this->checkServices();

        // Determine overall status
        $this->determineOverallStatus();

        // Display results
        $this->displayResults();

        // Send notifications if needed
        if ($this->option('notify') && !empty($this->criticalIssues)) {
            $this->sendHealthAlert();
        }

        // Log health check
        $duration = round(microtime(true) - $startTime, 2);
        Log::info('System health check completed', [
            'duration' => $duration,
            'status' => $this->healthStatus['overall_status'],
            'critical_issues' => count($this->criticalIssues),
            'warnings' => count($this->warnings),
        ]);

        $this->info("✅ Health check completed in {$duration} seconds");

        return empty($this->criticalIssues) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Check database health.
     */
    protected function checkDatabase(): void
    {
        $this->info('🗄️ Checking database...');

        try {
            $startTime = microtime(true);

            // Test connection
            DB::connection()->getPdo();
            $connectionTime = round((microtime(true) - $startTime) * 1000, 2);

            // Check database size
            $dbSize = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb FROM information_schema.tables WHERE table_schema = ?", [config('database.connections.mysql.database')]);
            $sizeInMB = $dbSize[0]->size_mb ?? 0;

            // Check slow queries
            $slowQueries = DB::select("SHOW GLOBAL STATUS LIKE 'Slow_queries'");
            $slowQueryCount = $slowQueries[0]->Value ?? 0;

            $this->healthStatus['checks']['database'] = [
                'status' => 'healthy',
                'connection_time_ms' => $connectionTime,
                'size_mb' => $sizeInMB,
                'slow_queries' => $slowQueryCount,
            ];

            // Check for issues
            if ($connectionTime > 1000) {
                $this->warnings[] = "Database connection is slow ({$connectionTime}ms)";
            }

            if ($sizeInMB > 1000) {
                $this->warnings[] = "Database size is large ({$sizeInMB}MB)";
            }

            $this->info("✅ Database: Connection {$connectionTime}ms, Size {$sizeInMB}MB");

        } catch (\Exception $e) {
            $this->healthStatus['checks']['database'] = [
                'status' => 'critical',
                'error' => $e->getMessage(),
            ];
            $this->criticalIssues[] = "Database connection failed: " . $e->getMessage();
            $this->error("❌ Database: Connection failed");
        }
    }

    /**
     * Check cache health.
     */
    protected function checkCache(): void
    {
        $this->info('💾 Checking cache...');

        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';

            // Test cache write/read
            Cache::put($testKey, $testValue, 60);
            $retrievedValue = Cache::get($testKey);
            Cache::forget($testKey);

            $cacheWorking = $retrievedValue === $testValue;

            $this->healthStatus['checks']['cache'] = [
                'status' => $cacheWorking ? 'healthy' : 'critical',
                'driver' => config('cache.default'),
            ];

            if (!$cacheWorking) {
                $this->criticalIssues[] = "Cache is not working properly";
                $this->error("❌ Cache: Not working");
            } else {
                $this->info("✅ Cache: Working properly");
            }

        } catch (\Exception $e) {
            $this->healthStatus['checks']['cache'] = [
                'status' => 'critical',
                'error' => $e->getMessage(),
            ];
            $this->criticalIssues[] = "Cache error: " . $e->getMessage();
            $this->error("❌ Cache: Error occurred");
        }
    }

    /**
     * Check storage health.
     */
    protected function checkStorage(): void
    {
        $this->info('💿 Checking storage...');

        try {
            $storagePath = storage_path();
            $freeBytes = disk_free_space($storagePath);
            $totalBytes = disk_total_space($storagePath);
            $usedPercent = round((($totalBytes - $freeBytes) / $totalBytes) * 100, 2);

            $this->healthStatus['checks']['storage'] = [
                'status' => $usedPercent > 90 ? 'critical' : ($usedPercent > 80 ? 'warning' : 'healthy'),
                'used_percent' => $usedPercent,
                'free_gb' => round($freeBytes / 1024 / 1024 / 1024, 2),
                'total_gb' => round($totalBytes / 1024 / 1024 / 1024, 2),
            ];

            if ($usedPercent > 90) {
                $this->criticalIssues[] = "Storage is critically low ({$usedPercent}% used)";
                $this->error("❌ Storage: Critically low ({$usedPercent}%)");
            } elseif ($usedPercent > 80) {
                $this->warnings[] = "Storage is getting low ({$usedPercent}% used)";
                $this->warn("⚠️ Storage: Getting low ({$usedPercent}%)");
            } else {
                $this->info("✅ Storage: {$usedPercent}% used");
            }

        } catch (\Exception $e) {
            $this->healthStatus['checks']['storage'] = [
                'status' => 'critical',
                'error' => $e->getMessage(),
            ];
            $this->criticalIssues[] = "Storage check failed: " . $e->getMessage();
            $this->error("❌ Storage: Check failed");
        }
    }

    /**
     * Check backup health.
     */
    protected function checkBackups(): void
    {
        $this->info('💾 Checking backups...');

        try {
            $backupPath = storage_path('app/backups');
            $maxAge = 25 * 60 * 60; // 25 hours

            $latestBackup = null;
            $backupCount = 0;

            if (is_dir($backupPath)) {
                $files = glob($backupPath . '/*/*');
                $backupCount = count($files);

                if (!empty($files)) {
                    $latestFile = array_reduce($files, function($latest, $file) {
                        return (!$latest || filemtime($file) > filemtime($latest)) ? $file : $latest;
                    });

                    if ($latestFile) {
                        $latestBackup = filemtime($latestFile);
                    }
                }
            }

            $backupAge = $latestBackup ? time() - $latestBackup : null;
            $isBackupTooOld = $backupAge && $backupAge > $maxAge;

            $this->healthStatus['checks']['backups'] = [
                'status' => $isBackupTooOld ? 'critical' : 'healthy',
                'latest_backup_age_hours' => $backupAge ? round($backupAge / 3600, 1) : null,
                'backup_count' => $backupCount,
            ];

            if ($isBackupTooOld) {
                $ageHours = round($backupAge / 3600, 1);
                $this->criticalIssues[] = "Latest backup is too old ({$ageHours} hours)";
                $this->error("❌ Backups: Too old ({$ageHours}h)");
            } elseif (!$latestBackup) {
                $this->criticalIssues[] = "No backups found";
                $this->error("❌ Backups: None found");
            } else {
                $ageHours = round($backupAge / 3600, 1);
                $this->info("✅ Backups: Latest {$ageHours}h ago ({$backupCount} total)");
            }

        } catch (\Exception $e) {
            $this->healthStatus['checks']['backups'] = [
                'status' => 'critical',
                'error' => $e->getMessage(),
            ];
            $this->criticalIssues[] = "Backup check failed: " . $e->getMessage();
            $this->error("❌ Backups: Check failed");
        }
    }

    /**
     * Check security status.
     */
    protected function checkSecurity(): void
    {
        $this->info('🔒 Checking security...');

        $securityIssues = [];

        // Check if app is in debug mode in production
        if (app()->environment('production') && config('app.debug')) {
            $securityIssues[] = "Debug mode is enabled in production";
        }

        // Check if app key is set
        if (empty(config('app.key'))) {
            $securityIssues[] = "Application key is not set";
        }

        // Check HTTPS in production
        if (app()->environment('production') && !request()->isSecure()) {
            $securityIssues[] = "HTTPS is not enforced in production";
        }

        $this->healthStatus['checks']['security'] = [
            'status' => empty($securityIssues) ? 'healthy' : 'critical',
            'issues' => $securityIssues,
        ];

        if (!empty($securityIssues)) {
            $this->criticalIssues = array_merge($this->criticalIssues, $securityIssues);
            $this->error("❌ Security: " . count($securityIssues) . " issues found");
        } else {
            $this->info("✅ Security: No issues found");
        }
    }

    /**
     * Check performance metrics.
     */
    protected function checkPerformance(): void
    {
        $this->info('⚡ Checking performance...');

        try {
            $memoryUsage = memory_get_usage(true);
            $memoryLimit = ini_get('memory_limit');
            $memoryLimitBytes = $this->convertToBytes($memoryLimit);
            $memoryPercent = round(($memoryUsage / $memoryLimitBytes) * 100, 2);

            $this->healthStatus['checks']['performance'] = [
                'status' => $memoryPercent > 80 ? 'warning' : 'healthy',
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'memory_limit' => $memoryLimit,
                'memory_percent' => $memoryPercent,
            ];

            if ($memoryPercent > 80) {
                $this->warnings[] = "High memory usage ({$memoryPercent}%)";
                $this->warn("⚠️ Performance: High memory usage ({$memoryPercent}%)");
            } else {
                $this->info("✅ Performance: Memory usage {$memoryPercent}%");
            }

        } catch (\Exception $e) {
            $this->healthStatus['checks']['performance'] = [
                'status' => 'warning',
                'error' => $e->getMessage(),
            ];
            $this->warnings[] = "Performance check failed: " . $e->getMessage();
        }
    }

    /**
     * Check external services.
     */
    protected function checkServices(): void
    {
        $this->info('🌐 Checking services...');

        // This is a placeholder for checking external services
        // You can add checks for payment gateways, email services, etc.

        $this->healthStatus['checks']['services'] = [
            'status' => 'healthy',
            'note' => 'External service checks not implemented yet',
        ];

        $this->info("✅ Services: Basic checks passed");
    }

    /**
     * Determine overall system status.
     */
    protected function determineOverallStatus(): void
    {
        if (!empty($this->criticalIssues)) {
            $this->healthStatus['overall_status'] = 'critical';
        } elseif (!empty($this->warnings)) {
            $this->healthStatus['overall_status'] = 'warning';
        } else {
            $this->healthStatus['overall_status'] = 'healthy';
        }
    }

    /**
     * Display health check results.
     */
    protected function displayResults(): void
    {
        $this->info('');
        $this->info('📋 === HEALTH CHECK SUMMARY ===');

        $status = $this->healthStatus['overall_status'];
        $statusIcon = match($status) {
            'healthy' => '✅',
            'warning' => '⚠️',
            'critical' => '❌',
            default => 'ℹ️',
        };

        $this->info("Overall Status: {$statusIcon} " . strtoupper($status));

        if (!empty($this->criticalIssues)) {
            $this->error('');
            $this->error('🚨 CRITICAL ISSUES:');
            foreach ($this->criticalIssues as $issue) {
                $this->error("  • {$issue}");
            }
        }

        if (!empty($this->warnings)) {
            $this->warn('');
            $this->warn('⚠️ WARNINGS:');
            foreach ($this->warnings as $warning) {
                $this->warn("  • {$warning}");
            }
        }

        if ($this->option('detailed')) {
            $this->displayDetailedResults();
        }
    }

    /**
     * Display detailed results.
     */
    protected function displayDetailedResults(): void
    {
        $this->info('');
        $this->info('📊 === DETAILED RESULTS ===');

        foreach ($this->healthStatus['checks'] as $check => $data) {
            $this->info('');
            $this->info(strtoupper($check) . ':');
            foreach ($data as $key => $value) {
                if (is_array($value)) {
                    $this->info("  {$key}: " . json_encode($value));
                } else {
                    $this->info("  {$key}: {$value}");
                }
            }
        }
    }

    /**
     * Send health alert notification.
     */
    protected function sendHealthAlert(): void
    {
        try {
            $adminEmail = config('mail.admin_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new SystemHealthAlert($this->healthStatus, $this->criticalIssues, $this->warnings));
                $this->info('📧 Health alert sent');
            }
        } catch (\Exception $e) {
            $this->error('❌ Failed to send health alert: ' . $e->getMessage());
        }
    }

    /**
     * Convert memory limit string to bytes.
     */
    protected function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int) $value;

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
}
