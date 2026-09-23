<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

interface ProductRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find product by slug
     *
     * @param string $slug
     * @return Product|null
     */
    public function findBySlug(string $slug): ?Product;

    /**
     * Find product by SKU
     *
     * @param string $sku
     * @return Product|null
     */
    public function findBySku(string $sku): ?Product;

    /**
     * Get products by category
     *
     * @param int $categoryId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCategory(int $categoryId, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products by brand
     *
     * @param int $brandId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByBrand(int $brandId, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get featured products
     *
     * @param int $limit
     * @return Collection
     */
    public function getFeatured(int $limit = 8): Collection;

    /**
     * Get latest products
     *
     * @param int $limit
     * @return Collection
     */
    public function getLatest(int $limit = 8): Collection;

    /**
     * Search products
     *
     * @param string $search
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $search, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithFilters(array $filters, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get related products
     *
     * @param Product $product
     * @param int $limit
     * @return Collection
     */
    public function getRelated(Product $product, int $limit = 4): Collection;

    /**
     * Get products by price range
     *
     * @param float $minPrice
     * @param float $maxPrice
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByPriceRange(float $minPrice, float $maxPrice, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products by color
     *
     * @param int $colorId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByColor(int $colorId, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products by size
     *
     * @param int $sizeId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getBySize(int $sizeId, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get out of stock products
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOutOfStock(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get low stock products
     *
     * @param int $threshold
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getLowStock(int $threshold = 10, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get top selling products
     *
     * @param int $limit
     * @return Collection
     */
    public function getTopSelling(int $limit = 10): Collection;

    /**
     * Get products with reviews
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithReviews(int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products by rating
     *
     * @param float $minRating
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByRating(float $minRating, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products on sale
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getOnSale(int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products by multiple categories
     *
     * @param array $categoryIds
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByCategories(array $categoryIds, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products by multiple brands
     *
     * @param array $brandIds
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByBrands(array $brandIds, int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products with specific stock status
     *
     * @param string $status
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getByStockStatus(string $status, int $perPage = 15): LengthAwarePaginator;

    /**
     * Update product stock
     *
     * @param int $productId
     * @param int $quantity
     * @return bool
     */
    public function updateStock(int $productId, int $quantity): bool;

    /**
     * Increment product views
     *
     * @param int $productId
     * @return bool
     */
    public function incrementViews(int $productId): bool;

    /**
     * Get products created between dates
     *
     * @param string $startDate
     * @param string $endDate
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getCreatedBetween(string $startDate, string $endDate, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get products with images
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithImages(int $perPage = 12): LengthAwarePaginator;

    /**
     * Get products without images
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getWithoutImages(int $perPage = 15): LengthAwarePaginator;

    /**
     * Get filtered query builder
     *
     * @param array $filters
     * @return Builder
     */
    public function getFilteredQuery(array $filters): Builder;
}
