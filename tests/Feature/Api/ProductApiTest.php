<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $category;
    protected $brand;
    protected $products;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->category = Category::factory()->create();
        $this->brand = Brand::factory()->create();
        $this->products = Product::factory(10)->create([
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);
    }

    public function test_can_get_products_list(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'slug',
                            'sku',
                            'regular_price',
                            'sale_price',
                            'current_price',
                            'stock_status',
                            'featured',
                            'category',
                            'brand'
                        ]
                    ],
                    'meta' => [
                        'total',
                        'count',
                        'per_page',
                        'current_page',
                        'currency'
                    ]
                ]);
    }

    public function test_can_get_single_product(): void
    {
        $product = $this->products->first();

        $response = $this->getJson("/api/v1/products/{$product->slug}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        'id',
                        'name',
                        'slug',
                        'description',
                        'regular_price',
                        'sale_price',
                        'category',
                        'brand'
                    ]
                ])
                ->assertJson([
                    'status' => 'success',
                    'data' => [
                        'id' => $product->id,
                        'name' => $product->name,
                        'slug' => $product->slug
                    ]
                ]);
    }

    public function test_returns_404_for_non_existent_product(): void
    {
        $response = $this->getJson('/api/v1/products/non-existent-product');

        $response->assertStatus(404)
                ->assertJson([
                    'status' => 'error',
                    'message' => 'Product not found'
                ]);
    }

    public function test_can_get_featured_products(): void
    {
        // Create featured products
        Product::factory(3)->create([
            'featured' => true,
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
        ]);

        $response = $this->getJson('/api/v1/products/featured');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'data' => [
                        '*' => [
                            'id',
                            'name',
                            'featured'
                        ]
                    ],
                    'meta'
                ]);
    }

    public function test_can_search_products(): void
    {
        $searchTerm = $this->products->first()->name;

        $response = $this->getJson("/api/v1/products/search?q={$searchTerm}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'meta'
                ]);
    }

    public function test_search_requires_query_parameter(): void
    {
        $response = $this->getJson('/api/v1/products/search');

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['q']);
    }

    public function test_can_filter_products_by_category(): void
    {
        $response = $this->getJson("/api/v1/categories/{$this->category->slug}/products");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data',
                    'meta'
                ]);
    }

    public function test_rate_limiting_works(): void
    {
        // Make multiple requests to test rate limiting
        for ($i = 0; $i < 65; $i++) {
            $response = $this->getJson('/api/v1/products');

            if ($i < 60) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429);
                break;
            }
        }
    }

    public function test_response_includes_performance_headers(): void
    {
        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
                ->assertHeader('X-Response-Time')
                ->assertHeader('X-Memory-Usage')
                ->assertHeader('X-Query-Count');
    }
}
