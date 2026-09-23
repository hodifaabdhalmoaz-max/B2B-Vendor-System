<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class WeeklyMaintenance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'maintenance:weekly
                            {--dry-run : Show what would be done without executing}
                            {--force : Skip confirmation prompts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform weekly maintenance tasks for optimal performance';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $startTime = microtime(true);
        $this->info('🔧 Starting weekly maintenance tasks...');

        if (!$this->option('force') && !$this->option('dry-run')) {
            if (!$this->confirm('This will perform maintenance tasks that may affect performance. Continue?')) {
                $this->info('Maintenance cancelled.');
                return Command::SUCCESS;
            }
        }

        $tasks = [
            'cleanupDatabase' => 'Database cleanup',
            'optimizeDatabase' => 'Database optimization',
            'cleanupStorage' => 'Storage cleanup',
            'optimizeImages' => 'Image optimization',
            'updateSearchIndex' => 'Search index update',
            'cleanupSessions' => 'Session cleanup',
            'generateReports' => 'Generate weekly reports',
            'checkSystemHealth' => 'System health check',
        ];

        $completedTasks = 0;
        $failedTasks = [];

        foreach ($tasks as $method => $description) {
            try {
                $this->info("📋 {$description}...");

                if (!$this->option('dry-run')) {
                    $this->$method();
                } else {
                    $this->info("   [DRY RUN] Would execute: {$description}");
                }

                $this->info("   ✅ Completed");
                $completedTasks++;

            } catch (\Exception $e) {
                $this->error("   ❌ Failed: " . $e->getMessage());
                $failedTasks[] = $description;
                Log::error("Weekly maintenance task failed: {$description}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->info('');
        $this->info('📊 === MAINTENANCE SUMMARY ===');
        $this->info("Duration: {$duration} seconds");
        $this->info("Completed: {$completedTasks}/" . count($tasks));

        if (!empty($failedTasks)) {
            $this->error("Failed tasks: " . implode(', ', $failedTasks));
        }

        Log::info('Weekly maintenance completed', [
            'duration' => $duration,
            'completed_tasks' => $completedTasks,
            'total_tasks' => count($tasks),
            'failed_tasks' => $failedTasks,
            'dry_run' => $this->option('dry-run'),
        ]);

        $this->info('🎉 Weekly maintenance completed!');

        return empty($failedTasks) ? Command::SUCCESS : Command::FAILURE;
    }

    /**
     * Clean up database records.
     */
    protected function cleanupDatabase(): void
    {
        // Clean old sessions
        DB::table('sessions')
            ->where('last_activity', '<', now()->subDays(30)->timestamp)
            ->delete();

        // Clean old password reset tokens
        DB::table('password_reset_tokens')
            ->where('created_at', '<', now()->subHours(24))
            ->delete();

        // Clean old failed jobs
        DB::table('failed_jobs')
            ->where('failed_at', '<', now()->subDays(7))
            ->delete();

        // Clean old audit logs (keep 90 days)
        if (DB::getSchemaBuilder()->hasTable('audit_logs')) {
            DB::table('audit_logs')
                ->where('created_at', '<', now()->subDays(90))
                ->delete();
        }

        // Clean old user activities (keep 90 days)
        if (DB::getSchemaBuilder()->hasTable('user_activities')) {
            DB::table('user_activities')
                ->where('created_at', '<', now()->subDays(90))
                ->delete();
        }

        // Clean old error logs (keep 30 days)
        if (DB::getSchemaBuilder()->hasTable('error_logs')) {
            DB::table('error_logs')
                ->where('created_at', '<', now()->subDays(30))
                ->delete();
        }

        // Clean old security logs (keep 180 days)
        if (DB::getSchemaBuilder()->hasTable('security_logs')) {
            DB::table('security_logs')
                ->where('created_at', '<', now()->subDays(180))
                ->delete();
        }

        // Clean old product views (keep 90 days)
        if (DB::getSchemaBuilder()->hasTable('product_views')) {
            DB::table('product_views')
                ->where('viewed_at', '<', now()->subDays(90))
                ->delete();
        }

        // Clean old cart items (abandoned carts older than 30 days)
        if (DB::getSchemaBuilder()->hasTable('shopping_cart')) {
            DB::table('shopping_cart')
                ->where('updated_at', '<', now()->subDays(30))
                ->delete();
        }

        // Clean old notifications
        if (DB::getSchemaBuilder()->hasTable('notifications')) {
            DB::table('notifications')
                ->where('created_at', '<', now()->subDays(60))
                ->whereNotNull('read_at')
                ->delete();
        }
    }

    /**
     * Optimize database tables.
     */
    protected function optimizeDatabase(): void
    {
        $tables = DB::select('SHOW TABLES');
        $databaseName = DB::getDatabaseName();

        foreach ($tables as $table) {
            $tableName = $table->{"Tables_in_{$databaseName}"};

            // Analyze table
            DB::statement("ANALYZE TABLE `{$tableName}`");

            // Optimize table
            DB::statement("OPTIMIZE TABLE `{$tableName}`");
        }
    }

    /**
     * Clean up storage files.
     */
    protected function cleanupStorage(): void
    {
        // Clean temporary files
        $tempFiles = Storage::disk('local')->files('temp');
        foreach ($tempFiles as $file) {
            if (Storage::disk('local')->lastModified($file) < now()->subDays(1)->timestamp) {
                Storage::disk('local')->delete($file);
            }
        }

        // Clean old log files
        $logFiles = Storage::disk('local')->files('logs');
        foreach ($logFiles as $file) {
            if (Storage::disk('local')->lastModified($file) < now()->subDays(30)->timestamp) {
                Storage::disk('local')->delete($file);
            }
        }

        // Clean old backup files (local only, keep cloud backups longer)
        $backupDirs = ['backups/database', 'backups/files'];
        foreach ($backupDirs as $dir) {
            if (Storage::disk('local')->exists($dir)) {
                $files = Storage::disk('local')->files($dir);
                foreach ($files as $file) {
                    if (Storage::disk('local')->lastModified($file) < now()->subDays(7)->timestamp) {
                        Storage::disk('local')->delete($file);
                    }
                }
            }
        }
    }

    /**
     * Optimize images.
     */
    protected function optimizeImages(): void
    {
        // This is a placeholder for image optimization
        // You can integrate with services like TinyPNG, ImageOptim, etc.

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $publicPath = public_path('uploads');

        if (!is_dir($publicPath)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($publicPath, \RecursiveDirectoryIterator::SKIP_DOTS)
        );

        $optimizedCount = 0;
        $batchSize = 100; // Process in batches to avoid memory issues
        $processedCount = 0;

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower($file->getExtension());
                if (in_array($extension, $imageExtensions)) {
                    // Check if file was modified in the last week
                    if ($file->getMTime() > strtotime('-1 week')) {
                        // Here you would call your image optimization service
                        // For now, we'll just count the files that would be optimized
                        $optimizedCount++;
                    }
                }

                // Clear memory periodically to prevent memory leaks
                $processedCount++;
                if ($processedCount % $batchSize === 0) {
                    gc_collect_cycles();
                }
            }
        }

        $this->info("   Found {$optimizedCount} images for optimization");
    }

    /**
     * Update search index.
     */
    protected function updateSearchIndex(): void
    {
        // Update product search index
        Artisan::call('scout:import', ['model' => 'App\\Models\\Product']);

        // Update category search index if using Scout
        if (class_exists('App\\Models\\Category')) {
            Artisan::call('scout:import', ['model' => 'App\\Models\\Category']);
        }
    }

    /**
     * Clean up expired sessions.
     */
    protected function cleanupSessions(): void
    {
        Artisan::call('session:gc');
    }

    /**
     * Generate weekly reports.
     */
    protected function generateReports(): void
    {
        // Generate sales report
        $startDate = now()->subWeek()->startOfWeek();
        $endDate = now()->subWeek()->endOfWeek();

        $salesData = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(*) as total_orders,
                SUM(total) as total_revenue,
                AVG(total) as average_order_value
            ')
            ->first();

        // Generate product performance report
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', 'completed')
            ->groupBy('order_items.product_id', 'products.name')
            ->selectRaw('
                products.name,
                SUM(order_items.quantity) as total_sold,
                SUM(order_items.price * order_items.quantity) as total_revenue
            ')
            ->orderBy('total_sold', 'desc')
            ->limit(10)
            ->get();

        // Store reports
        $reportData = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
            'sales' => $salesData,
            'top_products' => $topProducts,
            'generated_at' => now()->toISOString(),
        ];

        $reportPath = 'reports/weekly/' . $startDate->format('Y-m-d') . '_to_' . $endDate->format('Y-m-d') . '.json';
        Storage::disk('local')->put($reportPath, json_encode($reportData, JSON_PRETTY_PRINT));
    }

    /**
     * Perform system health check.
     */
    protected function checkSystemHealth(): void
    {
        Artisan::call('system:health-check', ['--detailed' => true]);
    }
}
