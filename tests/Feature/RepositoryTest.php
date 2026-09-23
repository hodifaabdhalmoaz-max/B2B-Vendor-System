<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Order;
use App\Models\User;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected $productRepository;
    protected $orderRepository;
    protected $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->productRepository = app(ProductRepositoryInterface::class);
        $this->orderRepository = app(OrderRepositoryInterface::class);
        $this->userRepository = app(UserRepositoryInterface::class);
    }

    /** @test */
    public function product_repository_can_find_by_slug()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create([
            'slug' => 'test-product-slug',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $foundProduct = $this->productRepository->findBySlug('test-product-slug');
        
        $this->assertNotNull($foundProduct);
        $this->assertEquals($product->id, $foundProduct->id);
        $this->assertEquals('test-product-slug', $foundProduct->slug);
    }

    /** @test */
    public function product_repository_can_find_by_sku()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create([
            'SKU' => 'TEST-SKU-123',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $foundProduct = $this->productRepository->findBySku('TEST-SKU-123');
        
        $this->assertNotNull($foundProduct);
        $this->assertEquals($product->id, $foundProduct->id);
        $this->assertEquals('TEST-SKU-123', $foundProduct->SKU);
    }

    /** @test */
    public function product_repository_can_get_by_category()
    {
        $category1 = Category::factory()->create();
        $category2 = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        Product::factory()->count(3)->create([
            'category_id' => $category1->id,
            'brand_id' => $brand->id,
        ]);
        
        Product::factory()->count(2)->create([
            'category_id' => $category2->id,
            'brand_id' => $brand->id,
        ]);

        $products = $this->productRepository->getByCategory($category1->id);
        
        $this->assertEquals(3, $products->count());
        foreach ($products as $product) {
            $this->assertEquals($category1->id, $product->category_id);
        }
    }

    /** @test */
    public function product_repository_can_get_featured_products()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        Product::factory()->count(3)->create([
            'featured' => true,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        
        Product::factory()->count(2)->create([
            'featured' => false,
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $featuredProducts = $this->productRepository->getFeatured(5);
        
        $this->assertEquals(3, $featuredProducts->count());
        foreach ($featuredProducts as $product) {
            $this->assertTrue($product->featured);
        }
    }

    /** @test */
    public function product_repository_can_search_products()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        Product::factory()->create([
            'name' => 'iPhone 13 Pro',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        
        Product::factory()->create([
            'name' => 'Samsung Galaxy S21',
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $results = $this->productRepository->search('iPhone');
        
        $this->assertEquals(1, $results->count());
        $this->assertStringContainsString('iPhone', $results->first()->name);
    }

    /** @test */
    public function product_repository_can_get_with_filters()
    {
        $category = Category::factory()->create(['slug' => 'electronics']);
        $brand = Brand::factory()->create(['slug' => 'apple']);
        
        Product::factory()->count(2)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'regular_price' => 1000,
        ]);
        
        Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'regular_price' => 500,
        ]);

        $filters = [
            'category' => 'electronics',
            'brand' => 'apple',
            'min_price' => 800,
            'max_price' => 1200,
        ];

        $results = $this->productRepository->getWithFilters($filters);
        
        $this->assertEquals(2, $results->count());
        foreach ($results as $product) {
            $this->assertEquals($category->id, $product->category_id);
            $this->assertEquals($brand->id, $product->brand_id);
            $this->assertGreaterThanOrEqual(800, $product->regular_price);
        }
    }

    /** @test */
    public function order_repository_can_get_by_user()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        Order::factory()->count(3)->create(['user_id' => $user1->id]);
        Order::factory()->count(2)->create(['user_id' => $user2->id]);

        $user1Orders = $this->orderRepository->getByUser($user1->id);
        
        $this->assertEquals(3, $user1Orders->count());
        foreach ($user1Orders as $order) {
            $this->assertEquals($user1->id, $order->user_id);
        }
    }

    /** @test */
    public function order_repository_can_get_by_status()
    {
        Order::factory()->count(3)->create(['status' => 'ordered']);
        Order::factory()->count(2)->create(['status' => 'delivered']);
        Order::factory()->count(1)->create(['status' => 'cancelled']);

        $orderedOrders = $this->orderRepository->getByStatus('ordered');
        
        $this->assertEquals(3, $orderedOrders->count());
        foreach ($orderedOrders as $order) {
            $this->assertEquals('ordered', $order->status);
        }
    }

    /** @test */
    public function order_repository_can_get_statistics()
    {
        Order::factory()->count(5)->create(['status' => 'ordered']);
        Order::factory()->count(3)->create(['status' => 'delivered', 'total' => 100]);
        Order::factory()->count(2)->create(['status' => 'cancelled']);

        $stats = $this->orderRepository->getStatistics();
        
        $this->assertEquals(10, $stats['total_orders']);
        $this->assertEquals(5, $stats['pending_orders']);
        $this->assertEquals(3, $stats['delivered_orders']);
        $this->assertEquals(2, $stats['cancelled_orders']);
        $this->assertEquals(300, $stats['total_revenue']);
    }

    /** @test */
    public function user_repository_can_find_by_email()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $foundUser = $this->userRepository->findByEmail('test@example.com');
        
        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
        $this->assertEquals('test@example.com', $foundUser->email);
    }

    /** @test */
    public function user_repository_can_get_by_type()
    {
        User::factory()->count(3)->create(['utype' => 'USR']);
        User::factory()->count(2)->create(['utype' => 'ADM']);

        $customers = $this->userRepository->getByType('USR');
        
        $this->assertEquals(3, $customers->count());
        foreach ($customers as $user) {
            $this->assertEquals('USR', $user->utype);
        }
    }

    /** @test */
    public function user_repository_can_get_statistics()
    {
        User::factory()->count(5)->create(['utype' => 'USR', 'email_verified_at' => now()]);
        User::factory()->count(2)->create(['utype' => 'ADM', 'email_verified_at' => now()]);
        User::factory()->count(3)->create(['utype' => 'USR', 'email_verified_at' => null]);

        $stats = $this->userRepository->getStatistics();
        
        $this->assertEquals(10, $stats['total_users']);
        $this->assertEquals(8, $stats['customer_users']);
        $this->assertEquals(2, $stats['admin_users']);
        $this->assertEquals(7, $stats['verified_users']);
        $this->assertEquals(3, $stats['unverified_users']);
    }

    /** @test */
    public function repositories_can_handle_pagination()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        Product::factory()->count(25)->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $products = $this->productRepository->paginate(10);
        
        $this->assertEquals(10, $products->count());
        $this->assertEquals(25, $products->total());
        $this->assertEquals(3, $products->lastPage());
    }

    /** @test */
    public function repositories_can_handle_relationships()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);

        $foundProduct = $this->productRepository->find($product->id);
        
        $this->assertNotNull($foundProduct);
        $this->assertInstanceOf(Category::class, $foundProduct->category);
        $this->assertInstanceOf(Brand::class, $foundProduct->brand);
    }
}
