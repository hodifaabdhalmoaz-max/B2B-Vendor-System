<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * SearchService — Centralized product search, filtering, and sorting.
 *
 * Design patterns applied:
 *  • Strategy Pattern  — sorting strategies are mapped declaratively.
 *  • Template Method   — searchProducts() orchestrates discrete steps.
 *  • Repository-like   — encapsulates all query-building logic.
 */
class SearchService
{
    /* ─────────────────────────────────────────────
     |  TABLE-EXISTS CACHE (per-request + long-term)
     * ───────────────────────────────────────────── */

    private static array $tableExistsCache = [];

    private function tableExists(string $table): bool
    {
        if (! isset(self::$tableExistsCache[$table])) {
            self::$tableExistsCache[$table] = Cache::remember(
                "table_exists_{$table}",
                86400,
                fn () => Schema::hasTable($table)
            );
        }

        return self::$tableExistsCache[$table];
    }

    /* ─────────────────────────────────────────────
     |  SORT STRATEGY MAP (Strategy Pattern)
     * ───────────────────────────────────────────── */

    /**
     * Each key is a sort_by value accepted from the frontend.
     * Each value is a callable that receives ($query, $direction).
     *
     * This eliminates the giant switch/case and makes adding
     * new sort criteria a one-liner.
     */
    private function getSortStrategies(): array
    {
        return [
            'relevance' => function (Builder $q, string $dir): void {
                $q->orderByDesc('featured')
                    ->orderBy('created_at', $dir === 'asc' ? 'asc' : 'desc');
            },

            'price' => function (Builder $q, string $dir): void {
                $q->orderByRaw('COALESCE(sale_price, regular_price) '.($dir === 'asc' ? 'ASC' : 'DESC'));
            },

            'newest' => function (Builder $q, string $dir): void {
                $q->orderBy('created_at', $dir === 'asc' ? 'asc' : 'desc');
            },

            'viewed' => function (Builder $q, string $dir): void {
                $q->orderBy('views', $dir === 'asc' ? 'asc' : 'desc');
            },

            'bestselling' => function (Builder $q, string $dir): void {
                // Sub-query: count sold items per product
                $q->withCount(['reviews as sold_count' => function ($sub) {
                    // Using order_items is more accurate for "bestselling"
                }]);
                $q->addSelect([
                    'total_sold' => DB::table('order_items')
                        ->selectRaw('COALESCE(SUM(quantity), 0)')
                        ->whereColumn('order_items.product_id', 'products.id'),
                ]);
                $q->orderBy('total_sold', $dir === 'asc' ? 'asc' : 'desc');
            },

            'name' => function (Builder $q, string $dir): void {
                $q->orderBy('name', $dir === 'asc' ? 'asc' : 'desc');
            },

            'stock' => function (Builder $q, string $dir): void {
                $q->orderBy('quantity', $dir === 'asc' ? 'asc' : 'desc');
            },
        ];
    }

    /** Allowed sort directions (whitelist). */
    private const ALLOWED_DIRECTIONS = ['asc', 'desc'];

    /* ─────────────────────────────────────────────
     |  MAIN SEARCH (Template Method)
     * ───────────────────────────────────────────── */

    /**
     * البحث المتقدم عن المنتجات مع دعم البحث الذكي والفلترة المتقدمة
     */
    public function searchProducts(array $params)
    {
        $query = $this->buildBaseQuery();

        $this->applyTextSearch($query, $params);
        $this->applyFilters($query, $params);
        $this->applySorting($query, $params);

        $perPage = $this->resolvePerPage($params);

        return $query->paginate($perPage)->appends(request()->query());
    }

    /* ─────────────────────────────────────────────
     |  QUERY BUILDING STEPS
     * ───────────────────────────────────────────── */

    /**
     * Base query with eager-loaded relationships and aggregates.
     */
    private function buildBaseQuery(): Builder
    {
        $relations = ['category', 'brand'];
        if ($this->tableExists('category_product')) {
            $relations[] = 'categories';
        }
        if ($this->tableExists('colors')) {
            $relations[] = 'colors';
        }
        if ($this->tableExists('sizes')) {
            $relations[] = 'sizes';
        }

        $query = Product::with($relations)
            ->withCount(['reviews as active_reviews_count' => fn ($q) => $q->where('status', true)])
            ->withAvg(['reviews as active_reviews_avg' => fn ($q) => $q->where('status', true)], 'rating');

        if (Schema::hasColumn('products', 'is_active')) {
            $query->where('is_active', true);
        }

        return $query;
    }

    /**
     * تطبيق البحث النصي المتقدم
     */
    private function applyTextSearch(Builder $query, array $params): void
    {
        $rawSearch = $params['search'] ?? null;
        if (empty($rawSearch)) {
            return;
        }

        $search = $this->sanitizeSearchTerm($rawSearch);
        $terms = $this->extractSearchTerms($search);

        $query->where(function (Builder $q) use ($search, $terms) {
            // البحث الأساسي في الحقول الرئيسية
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('SKU', 'like', "%{$search}%");

            // البحث في الفئات والعلامات التجارية
            $q->orWhereHas('category', fn ($cq) => $cq->where('name', 'like', "%{$search}%"))
                ->orWhereHas('brand', fn ($bq) => $bq->where('name', 'like', "%{$search}%"));

            // البحث في الألوان والأحجام
            if ($this->tableExists('colors')) {
                $q->orWhereHas('colors', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            }
            if ($this->tableExists('sizes')) {
                $q->orWhereHas('sizes', fn ($sq) => $sq->where('name', 'like', "%{$search}%"));
            }

            // البحث بالكلمات المفتاحية المتعددة
            if (count($terms) > 1) {
                foreach ($terms as $term) {
                    $q->orWhere('name', 'like', "%{$term}%")
                        ->orWhere('short_description', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%");
                }
            }
        });

        $this->logSearchQuery($search);
    }

    /**
     * تطبيق جميع الفلاتر (الفئة، الماركة، السعر، اللون، المقاس، إلخ)
     */
    private function applyFilters(Builder $query, array $params): void
    {
        // فلترة حسب الفئة
        $this->applyCategoryFilter($query, $params['categories'] ?? $params['category'] ?? null);

        // فلترة حسب العلامة التجارية
        $this->applyArrayFilter($query, 'brand_id', $params['brands'] ?? $params['brand'] ?? null);

        // فلترة حسب نطاق الأسعار
        if (isset($params['min_price']) || isset($params['max_price'])) {
            $min = (float) ($params['min_price'] ?? 0);
            $max = (float) ($params['max_price'] ?? 1_000_000);

            $query->where(function (Builder $q) use ($min, $max) {
                $q->where(fn ($sq) => $sq->whereNull('sale_price')->whereBetween('regular_price', [$min, $max]))
                    ->orWhere(fn ($sq) => $sq->whereNotNull('sale_price')->whereBetween('sale_price', [$min, $max]));
            });
        }

        // فلترة حسب حالة المخزون
        if (! empty($params['stock_status'])) {
            $query->where('stock_status', $params['stock_status']);
        }

        // فلترة المنتجات المميزة
        if (isset($params['featured']) && $params['featured'] !== '') {
            $query->where('featured', (bool) $params['featured']);
        }

        // فلترة العروض
        if (isset($params['is_offer']) && $params['is_offer'] !== '') {
            $query->where('is_offer', (bool) $params['is_offer']);
        }

        // فلترة حسب الألوان (many-to-many)
        if (! empty($params['colors']) && $this->tableExists('colors')) {
            $ids = $this->normalizeIds($params['colors']);
            $query->whereHas('colors', fn ($cq) => $cq->whereIn('colors.id', $ids));
        }

        // فلترة حسب الأحجام (many-to-many)
        if (! empty($params['sizes']) && $this->tableExists('sizes')) {
            $ids = $this->normalizeIds($params['sizes']);
            $query->whereHas('sizes', fn ($sq) => $sq->whereIn('sizes.id', $ids));
        }
    }

    /**
     * تطبيق الترتيب باستخدام Strategy Pattern.
     */
    private function applySorting(Builder $query, array $params): void
    {
        $sortBy = $params['sort_by'] ?? 'relevance';
        $direction = $params['sort_direction'] ?? 'desc';

        $strategies = $this->getSortStrategies();

        // Fallback to relevance if unknown sort_by
        if (! isset($strategies[$sortBy])) {
            $sortBy = 'relevance';
        }

        // Sanitize direction
        if (! in_array($direction, self::ALLOWED_DIRECTIONS, true)) {
            $direction = 'desc';
        }

        $strategies[$sortBy]($query, $direction);
    }

    /* ─────────────────────────────────────────────
     |  FILTER HELPERS (DRY)
     * ───────────────────────────────────────────── */

    /**
     * Generic whereIn filter for a column, accepting string|array|null.
     */
    private function applyArrayFilter(Builder $query, string $column, $value): void
    {
        if (empty($value)) {
            return;
        }
        $ids = $this->normalizeIds($value);
        $query->whereIn($column, $ids);
    }

    private function applyCategoryFilter(Builder $query, $value): void
    {
        if (empty($value)) {
            return;
        }

        $ids = $this->normalizeIds($value);

        $query->where(function (Builder $categoryQuery) use ($ids) {
            $categoryQuery->whereIn('category_id', $ids);

            if ($this->tableExists('category_product')) {
                $categoryQuery->orWhereHas('categories', fn ($query) => $query->whereIn('categories.id', $ids));
            }
        });
    }

    /**
     * Normalize comma-separated string or array to array of IDs.
     */
    private function normalizeIds($value): array
    {
        return is_array($value) ? $value : explode(',', (string) $value);
    }

    /**
     * Resolve per_page with sensible bounds.
     */
    private function resolvePerPage(array $params): int
    {
        $perPage = (int) ($params['per_page'] ?? 12);

        return ($perPage > 0 && $perPage <= 100) ? $perPage : 12;
    }

    /* ─────────────────────────────────────────────
     |  SEARCH HELPERS
     * ───────────────────────────────────────────── */

    /**
     * البحث المتقدم في الفئات مع دعم البحث الهجين
     */
    public function searchCategories(string $search, int $limit = 10)
    {
        $search = $this->sanitizeSearchTerm($search);

        return Category::where('name', 'like', "%{$search}%")
            ->orWhere('description', 'like', "%{$search}%")
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get();
    }

    /**
     * البحث المتقدم في العلامات التجارية
     */
    public function searchBrands(string $search, int $limit = 10)
    {
        $search = $this->sanitizeSearchTerm($search);

        return Brand::where('name', 'like', "%{$search}%")
            ->withCount('products')
            ->orderByDesc('products_count')
            ->limit($limit)
            ->get();
    }

    /**
     * البحث السريع مع الاقتراحات التلقائية
     */
    public function quickSearch(string $query, int $limit = 5): array
    {
        $search = $this->sanitizeSearchTerm($query);
        if (strlen($search) < 2) {
            return [];
        }

        return [
            'products' => Product::where('name', 'like', "%{$search}%")
                ->orWhere('short_description', 'like', "%{$search}%")
                ->select('id', 'name', 'slug', 'image', 'regular_price', 'sale_price')
                ->limit($limit)->get(),
            'categories' => Category::where('name', 'like', "%{$search}%")
                ->select('id', 'name', 'slug', 'image')
                ->limit(3)->get(),
            'brands' => Brand::where('name', 'like', "%{$search}%")
                ->select('id', 'name', 'slug', 'image')
                ->limit(3)->get(),
            'suggestions' => $this->getSearchSuggestions($search),
        ];
    }

    /**
     * الحصول على اقتراحات البحث الذكية
     */
    public function getSearchSuggestions(string $query): array
    {
        return array_merge(
            Product::where('name', 'like', "%{$query}%")->distinct()->limit(5)->pluck('name')->toArray(),
            Category::where('name', 'like', "%{$query}%")->limit(3)->pluck('name')->toArray(),
            Brand::where('name', 'like', "%{$query}%")->limit(3)->pluck('name')->toArray()
        );
    }

    /**
     * الحصول على المصطلحات الشائعة في البحث
     */
    public function getPopularSearchTerms(int $limit = 10): array
    {
        try {
            return Cache::remember('popular_search_terms', 3600, function () use ($limit) {
                return DB::table('statistics')
                    ->where('type', 'search')
                    ->select('data->query as term', DB::raw('COUNT(*) as count'))
                    ->groupBy('term')
                    ->orderByDesc('count')
                    ->limit($limit)
                    ->get()
                    ->pluck('count', 'term')
                    ->toArray();
            });
        } catch (\Exception $e) {
            Log::error('Error fetching popular search terms: '.$e->getMessage());

            return [];
        }
    }

    /* ─────────────────────────────────────────────
     |  FILTER OPTIONS (for modals/sidebars)
     * ───────────────────────────────────────────── */

    /**
     * الحصول على فلاتر البحث المتاحة
     */
    public function getSearchFilters(): array
    {
        return Cache::remember('search_filters', 1800, function () {
            $filters = [
                'categories' => Category::select('id', 'name', 'slug')->withCount('products')->orderBy('name')->get(),
                'brands' => Brand::select('id', 'name', 'slug')->withCount('products')->orderBy('name')->get(),
                'colors' => collect(),
                'sizes' => collect(),
                'price_ranges' => $this->getPriceRanges(),
            ];

            if ($this->tableExists('colors')) {
                $filters['colors'] = Color::select('id', 'name', 'hex_code')
                    ->whereHas('products')
                    ->orderBy('name')
                    ->get();
            }

            if ($this->tableExists('sizes')) {
                $filters['sizes'] = Size::select('id', 'name', 'code')
                    ->whereHas('products')
                    ->orderBy('order')
                    ->get();
            }

            return $filters;
        });
    }

    /**
     * الحصول على نطاقات الأسعار المتاحة
     */
    protected function getPriceRanges(): array
    {
        $maxPrice = Product::max('regular_price');
        if ($maxPrice <= 0) {
            return [];
        }

        $step = ceil($maxPrice / 5);
        $ranges = [];
        for ($i = 0; $i < 5; $i++) {
            $min = $i * $step;
            $max = ($i === 4) ? $maxPrice : ($i + 1) * $step;
            $ranges[] = [
                'min' => $min,
                'max' => $max,
                'label' => number_format($min).' - '.number_format($max).' ريال',
            ];
        }

        return $ranges;
    }

    /* ─────────────────────────────────────────────
     |  INTERNAL UTILITIES
     * ───────────────────────────────────────────── */

    /**
     * استخراج الكلمات المفتاحية من نص البحث
     */
    protected function extractSearchTerms(string $search): array
    {
        $terms = preg_split('/\s+/', trim($search));

        return array_unique(array_filter(
            array_map(fn ($t) => $this->sanitizeSearchTerm($t), $terms),
            fn ($t) => strlen($t) >= 2
        ));
    }

    /**
     * تسجيل عملية البحث للإحصائيات والتحليلات
     */
    protected function logSearchQuery(string $query): void
    {
        try {
            DB::table('statistics')->insert([
                'type' => 'search',
                'data' => json_encode([
                    'query' => $query,
                    'timestamp' => now(),
                    'user_agent' => request()->userAgent(),
                    'ip' => request()->ip(),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error logging search query: '.$e->getMessage());
        }
    }

    /**
     * تنظيف وتعقيم مصطلح البحث لمنع SQL injection
     */
    protected function sanitizeSearchTerm(string $term): string
    {
        $term = str_replace(['%', '_'], ['\\%', '\\_'], $term);
        $term = preg_replace('/[^\p{L}\p{N}\s\-_]/u', '', $term);

        return preg_replace('/\s+/', ' ', trim($term));
    }

    /**
     * البحث المتقدم مع دعم الفلترة المتعددة
     */
    public function advancedSearch(array $filters)
    {
        $cacheKey = 'advanced_search_'.md5(serialize($filters));

        return Cache::remember($cacheKey, 300, fn () => $this->searchProducts($filters));
    }

    /**
     * البحث بالباركود أو SKU
     */
    public function searchByCode(string $code): ?Product
    {
        $code = $this->sanitizeSearchTerm($code);

        return Product::where('SKU', $code)
            ->orWhere('SKU', 'like', "%{$code}%")
            ->with(['category', 'brand'])
            ->first();
    }

    /**
     * البحث في المنتجات ذات الصلة
     */
    public function getRelatedProducts(Product $product, int $limit = 8)
    {
        return Product::where('id', '!=', $product->id)
            ->where(fn ($q) => $q->where('category_id', $product->category_id)
                ->orWhere('brand_id', $product->brand_id))
            ->where('stock_status', 'instock')
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }
}
