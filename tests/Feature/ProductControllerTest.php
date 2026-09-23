<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\User;
use App\Models\Review;
use App\Services\StatisticService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Mockery;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the StatisticService
        $this->mock = Mockery::mock(StatisticService::class);
        $this->app->instance(StatisticService::class, $this->mock);
    }
    
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /**
     * Test product index page.
     */
    public function test_index_displays_products(): void
    {
        // Expect the logPageView method to be called
        $this->mock->shouldReceive('logPageView')->once();
        
        // Create some products
        $products = Product::factory()->count(5)->create();
        
        // Visit the products index page
        $response = $this->get(route('shop.index'));
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the view has the products
        $response->assertViewHas('products');
        
        // Assert all products are displayed
        foreach ($products as $product) {
            $response->assertSee($product->name);
        }
    }
    
    /**
     * Test product filtering by category.
     */
    public function test_index_filters_by_category(): void
    {
        // Expect the logPageView method to be called
        $this->mock->shouldReceive('logPageView')->once();
        
        // Create a category
        $category = Category::factory()->create();
        
        // Create products in the category
        $productsInCategory = Product::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);
        
        // Create products not in the category
        $productsNotInCategory = Product::factory()->count(2)->create();
        
        // Visit the products index page with category filter
        $response = $this->get(route('shop.index', ['category' => $category->slug]));
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the view has the products
        $response->assertViewHas('products');
        
        // Assert products in the category are displayed
        foreach ($productsInCategory as $product) {
            $response->assertSee($product->name);
        }
        
        // Assert products not in the category are not displayed
        foreach ($productsNotInCategory as $product) {
            $response->assertDontSee($product->name);
        }
    }
    
    /**
     * Test product search.
     */
    public function test_search_finds_products(): void
    {
        // Expect the logSearch method to be called
        $this->mock->shouldReceive('logSearch')->once();
        
        // Create products with specific names
        $product1 = Product::factory()->create(['name' => 'Test Product One']);
        $product2 = Product::factory()->create(['name' => 'Test Product Two']);
        $product3 = Product::factory()->create(['name' => 'Another Product']);
        
        // Search for products with "Test" in the name
        $response = $this->get(route('shop.search', ['search' => 'Test']));
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the view has the products
        $response->assertViewHas('products');
        
        // Assert the matching products are displayed
        $response->assertSee($product1->name);
        $response->assertSee($product2->name);
        
        // Assert the non-matching product is not displayed
        $response->assertDontSee($product3->name);
    }
    
    /**
     * Test product details page.
     */
    public function test_show_displays_product_details(): void
    {
        // Expect the logProductView method to be called
        $this->mock->shouldReceive('logProductView')->once();
        
        // Create a product
        $product = Product::factory()->create();
        
        // Create related products
        $relatedProducts = Product::factory()->count(2)->create();
        foreach ($relatedProducts as $relatedProduct) {
            $product->relatedProducts()->attach($relatedProduct->id);
        }
        
        // Create reviews for the product
        $reviews = Review::factory()->count(3)->create([
            'product_id' => $product->id,
        ]);
        
        // Visit the product details page
        $response = $this->get(route('shop.product.details', $product->slug));
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the view has the product
        $response->assertViewHas('product');
        
        // Assert the product details are displayed
        $response->assertSee($product->name);
        $response->assertSee($product->description);
        
        // Assert the related products are displayed
        foreach ($relatedProducts as $relatedProduct) {
            $response->assertSee($relatedProduct->name);
        }
        
        // Assert the reviews are displayed
        foreach ($reviews as $review) {
            $response->assertSee($review->comment);
        }
    }
    
    /**
     * Test adding a review to a product.
     */
    public function test_store_review_adds_review_to_product(): void
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a product
        $product = Product::factory()->create();
        
        // Review data
        $reviewData = [
            'rating' => 5,
            'title' => 'Great Product',
            'comment' => 'This is an excellent product!',
        ];
        
        // Submit a review as the authenticated user
        $response = $this->actingAs($user)
            ->post(route('product.review.store', $product->id), $reviewData);
        
        // Assert the user is redirected back
        $response->assertRedirect();
        
        // Assert the review was created in the database
        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'title' => 'Great Product',
            'comment' => 'This is an excellent product!',
        ]);
        
        // Assert the success message is flashed to the session
        $response->assertSessionHas('success', __('messages.review_added'));
    }
    
    /**
     * Test validation when adding a review.
     */
    public function test_store_review_validates_input(): void
    {
        // Create a user
        $user = User::factory()->create();
        
        // Create a product
        $product = Product::factory()->create();
        
        // Invalid review data (missing required fields)
        $reviewData = [
            'rating' => null,
            'title' => '',
            'comment' => '',
        ];
        
        // Submit an invalid review as the authenticated user
        $response = $this->actingAs($user)
            ->post(route('product.review.store', $product->id), $reviewData);
        
        // Assert the validation fails
        $response->assertSessionHasErrors(['rating', 'title', 'comment']);
        
        // Assert the review was not created in the database
        $this->assertDatabaseMissing('reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }
    
    /**
     * Test category page.
     */
    public function test_category_displays_products_in_category(): void
    {
        // Expect the logCategoryView method to be called
        $this->mock->shouldReceive('logCategoryView')->once();
        
        // Create a category
        $category = Category::factory()->create();
        
        // Create products in the category
        $productsInCategory = Product::factory()->count(3)->create([
            'category_id' => $category->id,
        ]);
        
        // Create products not in the category
        $productsNotInCategory = Product::factory()->count(2)->create();
        
        // Visit the category page
        $response = $this->get(route('shop.category', $category->slug));
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the view has the category and products
        $response->assertViewHas('category');
        $response->assertViewHas('products');
        
        // Assert products in the category are displayed
        foreach ($productsInCategory as $product) {
            $response->assertSee($product->name);
        }
        
        // Assert products not in the category are not displayed
        foreach ($productsNotInCategory as $product) {
            $response->assertDontSee($product->name);
        }
    }
    
    /**
     * Test brand page.
     */
    public function test_brand_displays_products_by_brand(): void
    {
        // Create a brand
        $brand = Brand::factory()->create();
        
        // Create products by the brand
        $productsByBrand = Product::factory()->count(3)->create([
            'brand_id' => $brand->id,
        ]);
        
        // Create products not by the brand
        $productsNotByBrand = Product::factory()->count(2)->create();
        
        // Visit the brand page
        $response = $this->get(route('shop.brand', $brand->slug));
        
        // Assert the response is successful
        $response->assertStatus(200);
        
        // Assert the view has the brand and products
        $response->assertViewHas('brand');
        $response->assertViewHas('products');
        
        // Assert products by the brand are displayed
        foreach ($productsByBrand as $product) {
            $response->assertSee($product->name);
        }
        
        // Assert products not by the brand are not displayed
        foreach ($productsNotByBrand as $product) {
            $response->assertDontSee($product->name);
        }
    }
}
