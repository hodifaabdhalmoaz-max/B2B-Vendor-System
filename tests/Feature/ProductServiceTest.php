<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Services\ProductService;
use App\Services\StatisticService;
use App\Services\AuditService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $productService;
    protected $user;
    protected $category;
    protected $brand;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test user
        $this->user = User::factory()->create();
        
        // Create test category and brand
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();
        
        // Initialize ProductService
        $this->productService = new ProductService(
            app(StatisticService::class),
            app(AuditService::class)
        );
    }

    /** @test */
    public function it_can_get_filtered_products()
    {
        // Create test products
        $product1 = Product::factory()->create([
            'name' => 'Test Product 1',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'regular_price' => 100,
            'sale_price' => 80,
        ]);

        $product2 = Product::factory()->create([
            'name' => 'Another Product',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'regular_price' => 200,
            'sale_price' => 150,
        ]);

        // Create request with search filter
        $request = new Request(['search' => 'Test']);
        
        $result = $this->productService->getFilteredProducts($request);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals('Test Product 1', $result->first()->name);
    }

    /** @test */
    public function it_can_get_product_by_slug()
    {
        $product = Product::factory()->create([
            'slug' => 'test-product',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $result = $this->productService->getProductBySlug('test-product');
        
        $this->assertEquals($product->id, $result->id);
        $this->assertEquals('test-product', $result->slug);
    }

    /** @test */
    public function it_can_get_related_products()
    {
        $mainProduct = Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        // Create related products in same category
        $relatedProducts = Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $result = $this->productService->getRelatedProducts($mainProduct, 4);
        
        $this->assertLessThanOrEqual(4, $result->count());
        $this->assertNotContains($mainProduct->id, $result->pluck('id'));
    }

    /** @test */
    public function it_can_search_products()
    {
        Product::factory()->create([
            'name' => 'iPhone 13',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        Product::factory()->create([
            'name' => 'Samsung Galaxy',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $result = $this->productService->searchProducts('iPhone');
        
        $this->assertEquals(1, $result->count());
        $this->assertStringContainsString('iPhone', $result->first()->name);
    }

    /** @test */
    public function it_can_get_products_by_category()
    {
        $category = Category::factory()->create(['slug' => 'electronics']);
        
        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'brand_id' => $this->brand->id,
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $result = $this->productService->getProductsByCategory('electronics');
        
        $this->assertEquals($category->id, $result['category']->id);
        $this->assertEquals(2, $result['products']->count());
    }

    /** @test */
    public function it_can_get_products_by_brand()
    {
        $brand = Brand::factory()->create(['slug' => 'apple']);
        
        Product::factory()->count(3)->create([
            'category_id' => $this->category->id,
            'brand_id' => $brand->id,
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $result = $this->productService->getProductsByBrand('apple');
        
        $this->assertEquals($brand->id, $result['brand']->id);
        $this->assertEquals(3, $result['products']->count());
    }

    /** @test */
    public function it_can_get_filter_options()
    {
        $result = $this->productService->getFilterOptions();
        
        $this->assertArrayHasKey('categories', $result);
        $this->assertArrayHasKey('brands', $result);
        $this->assertArrayHasKey('colors', $result);
        $this->assertArrayHasKey('sizes', $result);
    }

    /** @test */
    public function it_applies_price_range_filter()
    {
        Product::factory()->create([
            'regular_price' => 50,
            'sale_price' => 40,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        Product::factory()->create([
            'regular_price' => 150,
            'sale_price' => 120,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $request = new Request([
            'min_price' => 100,
            'max_price' => 200
        ]);
        
        $result = $this->productService->getFilteredProducts($request);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals(150, $result->first()->regular_price);
    }

    /** @test */
    public function it_applies_category_filter()
    {
        $category1 = Category::factory()->create(['slug' => 'category-1']);
        $category2 = Category::factory()->create(['slug' => 'category-2']);

        Product::factory()->create([
            'category_id' => $category1->id,
            'brand_id' => $this->brand->id,
        ]);

        Product::factory()->create([
            'category_id' => $category2->id,
            'brand_id' => $this->brand->id,
        ]);

        $request = new Request(['category' => 'category-1']);
        
        $result = $this->productService->getFilteredProducts($request);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals($category1->id, $result->first()->category_id);
    }

    /** @test */
    public function it_applies_brand_filter()
    {
        $brand1 = Brand::factory()->create(['slug' => 'brand-1']);
        $brand2 = Brand::factory()->create(['slug' => 'brand-2']);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $brand1->id,
        ]);

        Product::factory()->create([
            'category_id' => $this->category->id,
            'brand_id' => $brand2->id,
        ]);

        $request = new Request(['brand' => 'brand-1']);
        
        $result = $this->productService->getFilteredProducts($request);
        
        $this->assertEquals(1, $result->count());
        $this->assertEquals($brand1->id, $result->first()->brand_id);
    }

    /** @test */
    public function it_applies_sorting()
    {
        Product::factory()->create([
            'name' => 'Product A',
            'regular_price' => 100,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'created_at' => now()->subDays(2),
        ]);

        Product::factory()->create([
            'name' => 'Product B',
            'regular_price' => 200,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'created_at' => now()->subDay(),
        ]);

        // Test sorting by price ascending
        $request = new Request([
            'sort_by' => 'regular_price',
            'sort_direction' => 'asc'
        ]);
        
        $result = $this->productService->getFilteredProducts($request);
        
        $this->assertEquals(100, $result->first()->regular_price);
        $this->assertEquals(200, $result->last()->regular_price);
    }
}
