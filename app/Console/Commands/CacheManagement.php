<?php

namespace App\Console\Commands;

use App\Services\CacheService;
use Illuminate\Console\Command;

class CacheManagement extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:manage
                            {action : The action to perform (clear|warm|stats|flush-tags)}
                            {--tags=* : Cache tags to flush (when using flush-tags action)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage application cache (clear, warm up, statistics, flush by tags)';

    protected $cacheService;

    /**
     * Create a new command instance.
     */
    public function __construct(CacheService $cacheService)
    {
        parent::__construct();
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'clear':
                return $this->clearCache();
            case 'warm':
                return $this->warmUpCache();
            case 'stats':
                return $this->showStatistics();
            case 'flush-tags':
                return $this->flushTags();
            default:
                $this->error("Invalid action: {$action}");
                $this->info('Available actions: clear, warm, stats, flush-tags');
                return 1;
        }
    }

    /**
     * Clear all cache
     */
    protected function clearCache()
    {
        $this->info('Clearing all cache...');

        if ($this->cacheService->clearAll()) {
            $this->info('✅ Cache cleared successfully!');
            return 0;
        } else {
            $this->error('❌ Failed to clear cache!');
            return 1;
        }
    }

    /**
     * Warm up cache
     */
    protected function warmUpCache()
    {
        $this->info('Warming up cache...');

        $results = $this->cacheService->warmUp();

        if (isset($results['error'])) {
            $this->error('❌ Cache warm up failed: ' . $results['error']);
            return 1;
        }

        $this->info('✅ Cache warmed up successfully!');
        $this->table(
            ['Type', 'Count'],
            [
                ['Categories', $results['categories']->count()],
                ['Brands', $results['brands']->count()],
                ['Featured Products', $results['featured_products']->count()],
            ]
        );

        return 0;
    }

    /**
     * Show cache statistics
     */
    protected function showStatistics()
    {
        $this->info('Cache Statistics:');

        $stats = $this->cacheService->getStatistics();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Driver', $stats['driver']],
                ['Memory Usage', $stats['memory_usage']],
                ['Total Keys', $stats['total_keys']],
                ['Cache Hits', $stats['hits']],
                ['Cache Misses', $stats['misses']],
                ['Hit Ratio', $stats['hit_ratio']],
            ]
        );

        return 0;
    }

    /**
     * Flush cache by tags
     */
    protected function flushTags()
    {
        $tags = $this->option('tags');

        if (empty($tags)) {
            $this->error('No tags specified. Use --tags option.');
            $this->info('Available tags: ' . implode(', ', array_values(CacheService::CACHE_TAGS)));
            return 1;
        }

        $this->info('Flushing cache for tags: ' . implode(', ', $tags));

        if ($this->cacheService->flushTags($tags)) {
            $this->info('✅ Cache flushed successfully for specified tags!');
            return 0;
        } else {
            $this->error('❌ Failed to flush cache for specified tags!');
            return 1;
        }
    }
}
