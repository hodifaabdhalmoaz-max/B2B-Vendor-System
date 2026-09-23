<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Color;
use App\Models\Product;
use App\Models\Review;
use App\Models\Size;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ProductService
{
    protected $productRepository;

    protected $statisticService;

    protected $auditService;

    public function __construct(
        ProductRepositoryInterface|StatisticService $productRepository,
        StatisticService|AuditService $statisticService,
        ?AuditService $auditService = null
    ) {
        if ($productRepository instanceof StatisticService) {
            $this->productRepository = app(ProductRepositoryInterface::class);
            $this->statisticService = $productRepository;
            $this->auditService = $statisticService instanceof AuditService
                ? $statisticService
                : app(AuditService::class);

            return;
        }

        $this->productRepository = $productRepository;
        $this->statisticService = $statisticService instanceof StatisticService
            ? $statisticService
            : app(StatisticService::class);
        $this->auditService = $auditService ?? app(AuditService::class);
    }

    /**
     * Get filtered and paginated products
     */
    public function getFilteredProducts(Request $request): LengthAwarePaginator
    {
        $filters = [
            'category' => $request->category,
            'brand' => $request->brand,
            'color' => $request->color,
            'size' => $request->size,
            'min_price' => $request->min_price,
            'max_price' => $request->max_price,
            'search' => $request->search,
            'stock_status' => $request->stock_status,
            'featured' => $request->featured,
            'sort_by' => $request->sort_by ?? 'created_at',
            'sort_direction' => $request->sort_direction ?? 'desc',
        ];

        // Log search statistics if search is provided
        if (! empty($filters['search'])) {
            $this->logSearchStatistics($filters['search'], 0); // We'll get count from repository
        }

        $perPage = $request->per_page ?? 12;

        return $this->productRepository->getWithFilters($filters, $perPage);
    }

    /**
     * Log search statistics
     */
    protected function logSearchStatistics(string $search, int $resultsCount): void
    {
        $this->statisticService->logSearch($search, ['results_count' => $resultsCount]);
        $this->auditService->log('search', [
            'query' => $search,
            'results_count' => $resultsCount,
        ]);
    }

    /**
     * Get product by slug with related data
     */
    public function getProductBySlug(string $slug): Product
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Product not found');
        }

        // Log product view
        $this->logProductView($product);

        return $product;
    }

    /**
     * Log product view statistics
     */
    protected function logProductView(Product $product): void
    {
        $this->statisticService->logProductView($product->id);
        $this->auditService->log('product_view', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_price' => $product->current_price,
        ]);
    }

    /**
     * Get related products for a given product
     */
    public function getRelatedProducts(Product $product, int $limit = 4): Collection
    {
        return $this->productRepository->getRelated($product, $limit);
    }

    /**
     * Get product reviews with average rating
     */
    public function getProductReviews(Product $product): array
    {
        $reviews = $product->reviews()->active()->latest()->get();
        $avgRating = $reviews->avg('rating');

        return [
            'reviews' => $reviews,
            'average_rating' => $avgRating,
        ];
    }

    /**
     * Search products by term
     */
    public function searchProducts(string $search, int $perPage = 12): LengthAwarePaginator
    {
        $products = $this->productRepository->search($search, $perPage);

        // Log search statistics
        $resultsCount = $products->total();
        $this->statisticService->logSearch($search, ['results_count' => $resultsCount]);
        $this->auditService->log('search_page', [
            'query' => $search,
            'results_count' => $resultsCount,
        ]);

        return $products;
    }

    /**
     * Get products by category
     */
    public function getProductsByCategory(string $slug, int $perPage = 12): array
    {
        $category = Category::where('slug', $slug)->firstOrFail();
        $products = $this->productRepository->getByCategory($category->id, $perPage);

        // Log category view
        $this->statisticService->logCategoryView($category->id);
        $this->auditService->log('category_view', [
            'category_id' => $category->id,
            'category_name' => $category->name,
            'products_count' => $products->total(),
        ]);

        return [
            'category' => $category,
            'products' => $products,
        ];
    }

    /**
     * Get products by brand
     */
    public function getProductsByBrand(string $slug, int $perPage = 12): array
    {
        $brand = Brand::where('slug', $slug)->firstOrFail();
        $products = $this->productRepository->getByBrand($brand->id, $perPage);

        // Log brand view
        $this->auditService->log('brand_view', [
            'brand_id' => $brand->id,
            'brand_name' => $brand->name,
            'products_count' => $products->total(),
        ]);

        return [
            'brand' => $brand,
            'products' => $products,
        ];
    }

    /**
     * Get filter options for product listing
     */
    public function getFilterOptions(): array
    {
        return [
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
            'colors' => Color::active()->ordered()->get(),
            'sizes' => Size::active()->ordered()->get(),
        ];
    }

    /**
     * Store a review for a product
     */
    public function storeProductReview(Product $product, array $reviewData): Review
    {
        $review = new Review([
            'product_id' => $product->id,
            'user_id' => $reviewData['user_id'],
            'rating' => $reviewData['rating'],
            'title' => $reviewData['title'],
            'comment' => $reviewData['comment'],
            'status' => true, // Auto-approve reviews for now
        ]);

        $review->save();

        // Log review creation
        $this->auditService->log('review_added', [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'rating' => $reviewData['rating'],
            'review_id' => $review->id,
        ]);

        return $review;
    }
}
