<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Services\CartService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;
use Surfsidemedia\Shoppingcart\Facades\Cart;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->cartService = new CartService();
        
        // Clear cart before each test
        Cart::instance('cart')->destroy();
        Session::flush();
    }

    /** @test */
    public function it_can_add_product_to_cart()
    {
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 2,
            'price' => 100
        ];

        $this->cartService->addToCart($productData);

        $cartContents = $this->cartService->getCartContents();
        
        $this->assertEquals(1, $cartContents->count());
        $this->assertEquals(2, $this->cartService->getCartCount());
    }

    /** @test */
    public function it_can_increase_cart_quantity()
    {
        // Add product to cart first
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 100
        ];

        $this->cartService->addToCart($productData);
        $cartContents = $this->cartService->getCartContents();
        $rowId = $cartContents->first()->rowId;

        // Increase quantity
        $this->cartService->increaseQuantity($rowId);

        $this->assertEquals(2, $this->cartService->getCartCount());
    }

    /** @test */
    public function it_can_decrease_cart_quantity()
    {
        // Add product to cart first
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 3,
            'price' => 100
        ];

        $this->cartService->addToCart($productData);
        $cartContents = $this->cartService->getCartContents();
        $rowId = $cartContents->first()->rowId;

        // Decrease quantity
        $this->cartService->decreaseQuantity($rowId);

        $this->assertEquals(2, $this->cartService->getCartCount());
    }

    /** @test */
    public function it_prevents_quantity_from_going_below_one()
    {
        // Add product to cart first
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 100
        ];

        $this->cartService->addToCart($productData);
        $cartContents = $this->cartService->getCartContents();
        $rowId = $cartContents->first()->rowId;

        // Try to decrease quantity below 1
        $this->cartService->decreaseQuantity($rowId);

        $this->assertEquals(1, $this->cartService->getCartCount());
    }

    /** @test */
    public function it_can_remove_item_from_cart()
    {
        // Add product to cart first
        $productData = [
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 2,
            'price' => 100
        ];

        $this->cartService->addToCart($productData);
        $cartContents = $this->cartService->getCartContents();
        $rowId = $cartContents->first()->rowId;

        // Remove item
        $this->cartService->removeItem($rowId);

        $this->assertEquals(0, $this->cartService->getCartCount());
        $this->assertTrue($this->cartService->isEmpty());
    }

    /** @test */
    public function it_can_empty_entire_cart()
    {
        // Add multiple products to cart
        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Product 1',
            'quantity' => 2,
            'price' => 100
        ]);

        $this->cartService->addToCart([
            'id' => 2,
            'name' => 'Product 2',
            'quantity' => 1,
            'price' => 200
        ]);

        $this->assertEquals(3, $this->cartService->getCartCount());

        // Empty cart
        $this->cartService->emptyCart();

        $this->assertEquals(0, $this->cartService->getCartCount());
        $this->assertTrue($this->cartService->isEmpty());
    }

    /** @test */
    public function it_can_apply_valid_coupon()
    {
        // Create a valid coupon
        $coupon = Coupon::factory()->create([
            'code' => 'TEST10',
            'type' => 'percent',
            'value' => 10,
            'cart_value' => 50,
            'expiry_date' => Carbon::tomorrow(),
        ]);

        // Add product to cart
        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 100
        ]);

        $result = $this->cartService->applyCoupon('TEST10');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->cartService->hasCoupon());
        $this->assertEquals('TEST10', $this->cartService->getAppliedCoupon()['code']);
    }

    /** @test */
    public function it_rejects_invalid_coupon()
    {
        $result = $this->cartService->applyCoupon('INVALID');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->cartService->hasCoupon());
    }

    /** @test */
    public function it_rejects_expired_coupon()
    {
        // Create an expired coupon
        $coupon = Coupon::factory()->create([
            'code' => 'EXPIRED',
            'type' => 'fixed',
            'value' => 20,
            'cart_value' => 50,
            'expiry_date' => Carbon::yesterday(),
        ]);

        $result = $this->cartService->applyCoupon('EXPIRED');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->cartService->hasCoupon());
    }

    /** @test */
    public function it_rejects_coupon_when_cart_value_too_low()
    {
        // Create a coupon with high minimum cart value
        $coupon = Coupon::factory()->create([
            'code' => 'HIGHMIN',
            'type' => 'fixed',
            'value' => 20,
            'cart_value' => 200,
            'expiry_date' => Carbon::tomorrow(),
        ]);

        // Add low-value product to cart
        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 50
        ]);

        $result = $this->cartService->applyCoupon('HIGHMIN');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->cartService->hasCoupon());
    }

    /** @test */
    public function it_can_remove_coupon()
    {
        // Create and apply a coupon first
        $coupon = Coupon::factory()->create([
            'code' => 'REMOVE',
            'type' => 'fixed',
            'value' => 10,
            'cart_value' => 50,
            'expiry_date' => Carbon::tomorrow(),
        ]);

        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 100
        ]);

        $this->cartService->applyCoupon('REMOVE');
        $this->assertTrue($this->cartService->hasCoupon());

        // Remove coupon
        $result = $this->cartService->removeCoupon();

        $this->assertTrue($result['success']);
        $this->assertFalse($this->cartService->hasCoupon());
        $this->assertNull($this->cartService->getAppliedCoupon());
    }

    /** @test */
    public function it_calculates_discount_correctly_for_fixed_coupon()
    {
        $coupon = Coupon::factory()->create([
            'code' => 'FIXED20',
            'type' => 'fixed',
            'value' => 20,
            'cart_value' => 50,
            'expiry_date' => Carbon::tomorrow(),
        ]);

        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 100
        ]);

        $this->cartService->applyCoupon('FIXED20');
        $discounts = $this->cartService->getDiscountDetails();

        $this->assertEquals('20', $discounts['discount']);
    }

    /** @test */
    public function it_calculates_discount_correctly_for_percentage_coupon()
    {
        $coupon = Coupon::factory()->create([
            'code' => 'PERCENT15',
            'type' => 'percent',
            'value' => 15,
            'cart_value' => 50,
            'expiry_date' => Carbon::tomorrow(),
        ]);

        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 1,
            'price' => 100
        ]);

        $this->cartService->applyCoupon('PERCENT15');
        $discounts = $this->cartService->getDiscountDetails();

        $this->assertEquals('15', $discounts['discount']);
    }

    /** @test */
    public function it_can_get_cart_summary()
    {
        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 2,
            'price' => 100
        ]);

        $summary = $this->cartService->getCartSummary();

        $this->assertArrayHasKey('items', $summary);
        $this->assertArrayHasKey('count', $summary);
        $this->assertArrayHasKey('subtotal', $summary);
        $this->assertArrayHasKey('tax', $summary);
        $this->assertArrayHasKey('total', $summary);
        $this->assertArrayHasKey('has_coupon', $summary);
        
        $this->assertEquals(2, $summary['count']);
        $this->assertFalse($summary['has_coupon']);
    }

    /** @test */
    public function it_clears_cart_after_order()
    {
        // Create a valid coupon
        Coupon::factory()->create([
            'code' => 'TEST10',
            'type' => 'percent',
            'value' => 10,
            'cart_value' => 50,
            'expiry_date' => Carbon::tomorrow(),
        ]);

        $this->cartService->addToCart([
            'id' => 1,
            'name' => 'Test Product',
            'quantity' => 2,
            'price' => 100
        ]);

        $this->cartService->applyCoupon('TEST10');

        $this->assertEquals(2, $this->cartService->getCartCount());
        $this->assertTrue($this->cartService->hasCoupon());

        $this->cartService->clearCartAfterOrder();

        $this->assertEquals(0, $this->cartService->getCartCount());
        $this->assertFalse($this->cartService->hasCoupon());
        $this->assertTrue($this->cartService->isEmpty());
    }
}
