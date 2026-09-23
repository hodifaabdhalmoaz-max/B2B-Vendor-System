<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        // Get filtered products using ProductService
        $products = $this->productService->getFilteredProducts($request);

        // Get filter options
        $filterOptions = $this->productService->getFilterOptions();

        return view('shop', array_merge(
            compact('products'),
            $filterOptions
        ));
    }

    /**
     * Display the specified product.
     */
    public function show($slug)
    {
        // Get product with logging
        $product = $this->productService->getProductBySlug($slug);

        // Get related products
        $relatedProducts = $this->productService->getRelatedProducts($product);

        // Get product reviews with average rating
        $reviewData = $this->productService->getProductReviews($product);

        return view('details', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviewData['reviews'],
            'avgRating' => $reviewData['average_rating']
        ]);
    }

    /**
     * Store a review for the specified product.
     */
    public function storeReview(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'required|string|max:255',
            'comment' => 'required|string',
        ]);

        $product = Product::findOrFail($productId);

        $reviewData = [
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'title' => $request->title,
            'comment' => $request->comment,
        ];

        $this->productService->storeProductReview($product, $reviewData);

        return redirect()->back()->with('success', __('messages.review_added'));
    }

    /**
     * Search for products.
     */
    public function search(Request $request)
    {
        $search = $request->search;
        $products = $this->productService->searchProducts($search);

        return view('search', compact('products', 'search'));
    }

    /**
     * Display products by category.
     */
    public function category($slug)
    {
        $result = $this->productService->getProductsByCategory($slug);

        return view('category', [
            'category' => $result['category'],
            'products' => $result['products']
        ]);
    }

    /**
     * Display products by brand.
     */
    public function brand($slug)
    {
        $result = $this->productService->getProductsByBrand($slug);

        return view('brand', [
            'brand' => $result['brand'],
            'products' => $result['products']
        ]);
    }
}
