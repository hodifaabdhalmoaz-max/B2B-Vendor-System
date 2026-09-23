<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductCollection;
use App\Http\Resources\ProductResource;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\CategoryPageService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ApiProductController extends Controller
{
    protected $productRepository;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        private CategoryPageService $categoryPage
    ) {
        $this->productRepository = $productRepository;
    }

    /**
     * Display a listing of products.
     */
    public function index(Request $request): JsonResponse
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
            'featured' => $request->boolean('featured'),
            'sort_by' => $request->sort_by ?? 'created_at',
            'sort_direction' => $request->sort_direction ?? 'desc',
        ];

        $perPage = min($request->per_page ?? 12, 50); // Max 50 items per page
        $products = $this->productRepository->getWithFilters($filters, $perPage);

        return response()->json(new ProductCollection($products));
    }

    /**
     * Display the specified product.
     */
    public function show(string $slug): JsonResponse
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }

        // Load relationships for detailed view
        $product->load(['category', 'brand', 'colors', 'sizes', 'reviews.user']);

        return response()->json([
            'status' => 'success',
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Get featured products.
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min($request->limit ?? 8, 20); // Max 20 featured products
        $products = $this->productRepository->getFeatured($limit);

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products),
            'meta' => [
                'count' => $products->count(),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Get latest products.
     */
    public function latest(Request $request): JsonResponse
    {
        $limit = min($request->limit ?? 8, 20); // Max 20 latest products
        $products = $this->productRepository->getLatest($limit);

        return response()->json([
            'status' => 'success',
            'data' => ProductResource::collection($products),
            'meta' => [
                'count' => $products->count(),
                'limit' => $limit,
            ],
        ]);
    }

    /**
     * Search products.
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $perPage = min($request->per_page ?? 12, 50);
        $products = $this->productRepository->search($request->q, $perPage);

        return response()->json(new ProductCollection($products));
    }

    /**
     * Get products by category.
     */
    public function byCategory(string $categorySlug, Request $request): JsonResponse
    {
        try {
            $data = $this->categoryPage->getPageData($request, $categorySlug);
        } catch (ModelNotFoundException) {
            return response()->json([
                'status' => 'error',
                'message' => 'Category not found',
            ], 404);
        }

        $products = new ProductCollection($data['products']);
        $paginatedProducts = $products->toArray($request);

        return response()->json([
            'status' => 'success',
            'data' => [
                'selected_category' => new CategoryResource($data['category']),
                'top_categories' => CategoryResource::collection($data['topCategories']),
                'products' => $paginatedProducts,
            ],
            'meta' => $paginatedProducts['meta'],
            'links' => $paginatedProducts['links'],
        ]);
    }

    /**
     * Get products by brand.
     */
    public function byBrand(string $brandSlug, Request $request): JsonResponse
    {
        $brand = \App\Models\Brand::where('slug', $brandSlug)->first();

        if (! $brand) {
            return response()->json([
                'status' => 'error',
                'message' => 'Brand not found',
            ], 404);
        }

        $perPage = min($request->per_page ?? 12, 50);
        $products = $this->productRepository->getByBrand($brand->id, $perPage);

        return response()->json(new ProductCollection($products));
    }
}
