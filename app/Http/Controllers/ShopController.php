<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Services\SearchService;
use App\Services\StatisticService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * ShopController — handles all storefront browsing, search, and category views.
 *
 * Uses SearchService as a single source of truth for querying products.
 * Sorting/filter parameter resolution is centralised via resolveSortParams().
 */
class ShopController extends Controller
{
    private SearchService $search;

    private StatisticService $statistics;

    public function __construct(SearchService $search, StatisticService $statistics)
    {
        $this->search = $search;
        $this->statistics = $statistics;
    }

    /* ──────────────────────────────────────────
     |  SHARED PARAM RESOLVER (DRY)
     * ────────────────────────────────────────── */

    /**
     * Normalise sort/filter params from the request into a
     * canonical format understood by SearchService.
     *
     * Handles both legacy numeric order values and the new string-based ones.
     */
    private function resolveSortParams(Request $request, array $params): array
    {
        $params['per_page'] = $request->query('size') ?: $request->query('per_page', 12);

        // Accept 'order' from sort modal, or 'sort_by' directly
        $sortBy = $request->query('order') ?: $request->query('sort_by', 'newest');
        $sortDir = $request->query('order_dir') ?: $request->query('sort_direction', 'desc');

        // Legacy numeric mapping (backwards compatibility)
        if (is_numeric($sortBy)) {
            $sortBy = [1 => 'newest', 2 => 'oldest', 3 => 'price', 4 => 'price'][$sortBy] ?? 'newest';
            if ($sortBy === 'price') {
                $sortDir = ($sortBy == 3) ? 'asc' : 'desc';
            }
        }

        $params['sort_by'] = $sortBy;
        $params['sort_direction'] = $sortDir;

        if (! empty($params['category']) && empty($params['categories'])) {
            $category = Category::where('slug', $params['category'])->first();
            $params['categories'] = $category?->id;
        }

        if (! empty($params['brand']) && empty($params['brands'])) {
            $brand = Brand::where('slug', $params['brand'])->first();
            $params['brands'] = $brand?->id;
        }

        return $params;
    }

    /**
     * Build the shared view data for sort/filter modals.
     */
    private function buildViewData(Request $request, $products, array $extra = []): array
    {
        $filters = $this->search->getSearchFilters();

        return array_merge([
            'products' => $products,
            'brands' => $filters['brands'],
            'categories' => $filters['categories'],
            'colors' => $filters['colors'],
            'sizes' => $filters['sizes'],
            'min_price' => $request->query('min_price', 0),
            'max_price' => $request->query('max_price', 500000),
            'f_brands' => $request->query('brands', ''),
            'f_categories' => $request->query('categories', ''),
            'size' => $request->query('size', 12),
            'order' => $request->query('order', 'newest'),
            'order_dir' => $request->query('order_dir', 'desc'),
        ], $extra);
    }

    /* ──────────────────────────────────────────
     |  SHOP INDEX
     * ────────────────────────────────────────── */

    public function index(Request $request)
    {
        $this->statistics->logPageView('shop', [
            'filters' => $request->query(),
        ]);

        $params = $this->resolveSortParams($request, $request->all());
        $products = $this->search->searchProducts($params);

        return view('shop', $this->buildViewData($request, $products));
    }

    /* ──────────────────────────────────────────
     |  PRODUCT DETAILS
     * ────────────────────────────────────────── */

    public function product_details(string $product_slug)
    {
        $product = Product::with([
            'brand', 'category',
            'colorImages',
            'colors' => fn ($q) => $q->active()->ordered(),
            'sizes' => fn ($q) => $q->active()->ordered(),
            'reviews' => fn ($q) => $q->where('status', true)->with('user')->latest(),
        ])->where('slug', $product_slug)->firstOrFail();

        $this->statistics->logProductView($product->id, [
            'slug' => $product->slug,
        ]);

        $explicitRelated = $product->relatedProducts()
            ->with(['brand', 'category'])
            ->withCount(['colors', 'sizes'])
            ->limit(8)
            ->get();

        $categoryRelated = Product::where('slug', '<>', $product_slug)
            ->where('category_id', $product->category_id)
            ->select(['id', 'name', 'slug', 'short_description', 'regular_price', 'sale_price', 'image', 'images', 'category_id', 'brand_id'])
            ->withCount(['colors', 'sizes'])
            ->withCount(['reviews as active_reviews_count' => fn ($q) => $q->where('status', true)])
            ->withAvg(['reviews as active_reviews_avg' => fn ($q) => $q->where('status', true)], 'rating')
            ->inRandomOrder()
            ->limit(8)
            ->get();

        $rproducts = $explicitRelated
            ->merge($categoryRelated)
            ->unique('id')
            ->take(8)
            ->values();

        $defaultAddress = auth()->check()
            ? Address::forUser(auth()->id())->default()->first()
            : null;

        return view('details', compact('product', 'rproducts', 'defaultAddress'));
    }

    /* ──────────────────────────────────────────
     |  SEARCH
     * ────────────────────────────────────────── */

    public function search(Request $request)
    {
        $this->statistics->logSearch($request->query('search', ''), [
            'source' => 'shop',
        ]);

        $params = $this->resolveSortParams($request, $request->all());

        // Dynamic tab filtering
        if ($request->has('tab') && $request->tab !== 'all') {
            $params['search'] = trim(($request->search ?? '').' '.$request->tab);
        }

        $products = $this->search->searchProducts($params);
        $searchQuery = $request->query('search');

        // Generate dynamic tabs from matching product names
        $tabs = collect();
        if ($searchQuery) {
            $tabs = Product::where('name', 'LIKE', "%{$searchQuery}%")
                ->pluck('name')
                ->map(fn ($name) => explode(' ', trim($name))[0])
                ->unique()
                ->filter(fn ($word) => mb_strlen($word) > 2)
                ->take(10)
                ->values();
        }

        return view('search-results', $this->buildViewData($request, $products, [
            'tabs' => $tabs,
            'current_tab' => $request->query('tab', 'all'),
            'search_query' => $searchQuery,
        ]));
    }

    /**
     * البحث السريع مع الاقتراحات التلقائية (AJAX)
     */
    public function quickSearch(Request $request)
    {
        $request->validate(['q' => 'required|string|min:2|max:100']);

        return response()->json([
            'success' => true,
            'data' => $this->search->quickSearch($request->q, 8),
        ]);
    }

    /* ──────────────────────────────────────────
     |  CATEGORIES
     * ────────────────────────────────────────── */

    public function categories(Request $request)
    {
        $categoryContexts = [
            'all' => 'home.all',
            'kids' => 'kids.category',
            'gifts' => 'gifts.category',
            'toys' => 'toys.category',
            'mother' => 'mother.category',
            'care' => 'care.category',
            'accessories' => 'accessories.category',
            'shoes' => 'shoes.category',
        ];

        $activeMarketCategory = in_array($request->query('market_tab'), ['kids', 'gifts', 'toys', 'mother', 'care', 'accessories', 'shoes'], true)
            ? $request->query('market_tab')
            : 'all';

        $categories = Cache::remember('shop_all_categories_market_v2', 1800, fn () => Category::select('id', 'name', 'slug', 'image', 'status', 'storefront_contexts', 'storefront_order')
            ->withCount('products')
            ->where(function ($query) {
                $query->where('status', 'active')->orWhereNull('status');
            })
            ->orderBy('storefront_order')
            ->orderBy('name')
            ->get()
        );

        $activeContext = $categoryContexts[$activeMarketCategory] ?? null;
        $visibleCategories = $activeContext
            ? $categories->filter(fn ($category) => in_array($activeContext, $category->storefront_contexts ?? [], true))->values()
            : $categories;
        $visibleCategoryIds = $visibleCategories->pluck('id')->values();
        $selectedCategoryId = (int) $request->query('category_id', 0);
        $selectedCategory = $selectedCategoryId > 0
            ? $visibleCategories->firstWhere('id', $selectedCategoryId)
            : null;
        $selectedCategoryId = $selectedCategory?->id;

        $productSectionPrefix = $activeMarketCategory === 'all' ? 'home.' : "{$activeMarketCategory}.";
        $productSections = collect(array_keys(config('storefront.product_sections', [])))
            ->filter(fn ($section) => str_starts_with($section, $productSectionPrefix))
            ->values();

        $storefrontCacheVersion = (int) Cache::get('storefront_cache_version', 1);
        $recommendedCacheKey = 'categories_recommended_products_v3_'.$storefrontCacheVersion.'_'.$activeMarketCategory.'_'.($selectedCategoryId ?: 'all');

        $recommendedProducts = Cache::remember($recommendedCacheKey, 900, fn () => Product::active()
            ->select(['id', 'name', 'slug', 'short_description', 'regular_price', 'sale_price', 'image', 'images', 'category_id', 'brand_id', 'featured', 'is_offer', 'quantity', 'storefront_sections', 'storefront_order', 'created_at'])
            ->whereNotNull('storefront_sections')
            ->when($productSections->isNotEmpty(), function ($query) use ($productSections) {
                $query->where(function ($sectionQuery) use ($productSections) {
                    foreach ($productSections as $section) {
                        $sectionQuery->orWhereJsonContains('storefront_sections', $section);
                    }
                });
            })
            ->when($selectedCategoryId, fn ($query) => $query->where('category_id', $selectedCategoryId))
            ->when(! $selectedCategoryId && $visibleCategoryIds->isNotEmpty(), fn ($query) => $query->whereIn('category_id', $visibleCategoryIds))
            ->when(! $selectedCategoryId && $visibleCategoryIds->isEmpty(), fn ($query) => $query->whereRaw('1 = 0'))
            ->with(['colors'])
            ->withCount(['reviews as active_reviews_count' => function ($query) {
                $query->where('status', true);
            }])
            ->withAvg(['reviews as active_reviews_avg' => function ($query) {
                $query->where('status', true);
            }], 'rating')
            ->orderBy('storefront_order')
            ->orderByDesc('featured')
            ->orderByDesc('created_at')
            ->take(12)
            ->get()
        );

        $contentCategories = $selectedCategory
            ? collect([$selectedCategory])
            : $visibleCategories;

        return view('categories', compact('categories', 'visibleCategories', 'contentCategories', 'recommendedProducts', 'activeMarketCategory', 'selectedCategory'));
    }

    /* ──────────────────────────────────────────
     |  OFFERS
     * ────────────────────────────────────────── */

    public function offers(Request $request)
    {
        $params = $this->resolveSortParams($request, $request->all());
        $params['is_offer'] = 1;

        // Dynamic tab filtering for categories
        if ($request->has('tab') && $request->tab !== 'all') {
            $category = Category::where('slug', $request->tab)->first();
            if ($category) {
                $params['categories'] = $category->id;
            }
        }

        $products = $this->search->searchProducts($params);

        // Get categories for tabs that have offer products
        $tabs = Category::whereHas('products', function ($query) {
            $query->where('is_offer', 1);
        })->select('name', 'slug')->get();

        return view('offers', $this->buildViewData($request, $products, [
            'tabs' => $tabs,
            'current_tab' => $request->query('tab', 'all'),
        ]));
    }

    /* ──────────────────────────────────────────
     |  BRAND
     * ────────────────────────────────────────── */

    public function brand(Request $request, string $slug)
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();

        $params = $this->resolveSortParams($request, $request->all());
        $params['brands'] = $brand->id;

        $products = $this->search->searchProducts($params);

        return view('shop', $this->buildViewData($request, $products, [
            'brand' => $brand,
        ]));
    }
}
