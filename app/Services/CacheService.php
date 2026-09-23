<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheService
{
    /**
     * Cache durations in seconds
     */
    const CACHE_DURATIONS = [
        'short' => 300,      // 5 minutes
        'medium' => 1800,    // 30 minutes
        'long' => 3600,      // 1 hour
        'daily' => 86400,    // 24 hours
        'weekly' => 604800,  // 7 days
    ];

    /**
     * Cache tags for organized cache management
     */
    const CACHE_TAGS = [
        'products' => 'products',
        'categories' => 'categories',
        'brands' => 'brands',
        'orders' => 'orders',
        'users' => 'users',
        'statistics' => 'statistics',
        'settings' => 'settings',
    ];

    /**
     * Get cache key with prefix
     *
     * @param string $key
     * @param array $params
     * @return string
     */
    public function getCacheKey(string $key, array $params = []): string
    {
        $prefix = config('app.name', 'laravel') . '_cache';
        $paramString = empty($params) ? '' : '_' . md5(serialize($params));
        
        return strtolower($prefix . '_' . $key . $paramString);
    }

    /**
     * Remember cache with tags
     *
     * @param string $key
     * @param mixed $callback
     * @param string $duration
     * @param array $tags
     * @param array $params
     * @return mixed
     */
    public function remember(string $key, $callback, string $duration = 'medium', array $tags = [], array $params = [])
    {
        $cacheKey = $this->getCacheKey($key, $params);
        $cacheDuration = self::CACHE_DURATIONS[$duration] ?? self::CACHE_DURATIONS['medium'];

        try {
            if (!empty($tags) && $this->supportsTagging()) {
                return Cache::tags($tags)->remember($cacheKey, $cacheDuration, $callback);
            }

            return Cache::remember($cacheKey, $cacheDuration, $callback);
        } catch (\Exception $e) {
            Log::warning('Cache operation failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);

            // Fallback to direct callback execution
            return is_callable($callback) ? $callback() : $callback;
        }
    }

    /**
     * Put value in cache with tags
     *
     * @param string $key
     * @param mixed $value
     * @param string $duration
     * @param array $tags
     * @param array $params
     * @return bool
     */
    public function put(string $key, $value, string $duration = 'medium', array $tags = [], array $params = []): bool
    {
        $cacheKey = $this->getCacheKey($key, $params);
        $cacheDuration = self::CACHE_DURATIONS[$duration] ?? self::CACHE_DURATIONS['medium'];

        try {
            if (!empty($tags) && $this->supportsTagging()) {
                return Cache::tags($tags)->put($cacheKey, $value, $cacheDuration);
            }

            return Cache::put($cacheKey, $value, $cacheDuration);
        } catch (\Exception $e) {
            Log::warning('Cache put operation failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get value from cache
     *
     * @param string $key
     * @param mixed $default
     * @param array $params
     * @return mixed
     */
    public function get(string $key, $default = null, array $params = [])
    {
        $cacheKey = $this->getCacheKey($key, $params);

        try {
            return Cache::get($cacheKey, $default);
        } catch (\Exception $e) {
            Log::warning('Cache get operation failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);

            return $default;
        }
    }

    /**
     * Forget cache by key
     *
     * @param string $key
     * @param array $params
     * @return bool
     */
    public function forget(string $key, array $params = []): bool
    {
        $cacheKey = $this->getCacheKey($key, $params);

        try {
            return Cache::forget($cacheKey);
        } catch (\Exception $e) {
            Log::warning('Cache forget operation failed', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Flush cache by tags
     *
     * @param array $tags
     * @return bool
     */
    public function flushTags(array $tags): bool
    {
        try {
            if ($this->supportsTagging()) {
                Cache::tags($tags)->flush();
                return true;
            }

            // Fallback: clear all cache if tagging not supported
            Cache::flush();
            return true;
        } catch (\Exception $e) {
            Log::warning('Cache flush tags operation failed', [
                'tags' => $tags,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Check if cache driver supports tagging
     *
     * @return bool
     */
    public function supportsTagging(): bool
    {
        $driver = config('cache.default');
        $supportedDrivers = ['redis', 'memcached', 'dynamodb'];
        
        return in_array($driver, $supportedDrivers);
    }

    /**
     * Get cache statistics
     *
     * @return array
     */
    public function getStatistics(): array
    {
        try {
            $store = Cache::getStore();
            
            if (method_exists($store, 'getRedis')) {
                $redis = $store->getRedis();
                $info = $redis->info();
                
                return [
                    'driver' => 'redis',
                    'memory_usage' => $info['used_memory_human'] ?? 'N/A',
                    'total_keys' => $info['db0']['keys'] ?? 0,
                    'hits' => $info['keyspace_hits'] ?? 0,
                    'misses' => $info['keyspace_misses'] ?? 0,
                    'hit_ratio' => $this->calculateHitRatio($info['keyspace_hits'] ?? 0, $info['keyspace_misses'] ?? 0),
                ];
            }

            return [
                'driver' => config('cache.default'),
                'memory_usage' => 'N/A',
                'total_keys' => 'N/A',
                'hits' => 'N/A',
                'misses' => 'N/A',
                'hit_ratio' => 'N/A',
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to get cache statistics', [
                'error' => $e->getMessage()
            ]);

            return [
                'driver' => config('cache.default'),
                'error' => 'Failed to retrieve statistics',
            ];
        }
    }

    /**
     * Calculate cache hit ratio
     *
     * @param int $hits
     * @param int $misses
     * @return string
     */
    protected function calculateHitRatio(int $hits, int $misses): string
    {
        $total = $hits + $misses;
        
        if ($total === 0) {
            return '0%';
        }

        $ratio = ($hits / $total) * 100;
        return number_format($ratio, 2) . '%';
    }

    /**
     * Clear all cache
     *
     * @return bool
     */
    public function clearAll(): bool
    {
        try {
            Cache::flush();
            Log::info('All cache cleared successfully');
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to clear all cache', [
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Warm up cache with common data
     *
     * @return array
     */
    public function warmUp(): array
    {
        $results = [];

        try {
            // Warm up categories
            $results['categories'] = $this->remember(
                'categories_all',
                fn() => \App\Models\Category::orderBy('name')->get(),
                'daily',
                [self::CACHE_TAGS['categories']]
            );

            // Warm up brands
            $results['brands'] = $this->remember(
                'brands_all',
                fn() => \App\Models\Brand::orderBy('name')->get(),
                'daily',
                [self::CACHE_TAGS['brands']]
            );

            // Warm up featured products
            $results['featured_products'] = $this->remember(
                'products_featured',
                fn() => \App\Models\Product::where('featured', true)->with(['category', 'brand'])->get(),
                'long',
                [self::CACHE_TAGS['products']]
            );

            Log::info('Cache warm up completed successfully', [
                'categories_count' => $results['categories']->count(),
                'brands_count' => $results['brands']->count(),
                'featured_products_count' => $results['featured_products']->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Cache warm up failed', [
                'error' => $e->getMessage()
            ]);

            $results['error'] = $e->getMessage();
        }

        return $results;
    }
}
