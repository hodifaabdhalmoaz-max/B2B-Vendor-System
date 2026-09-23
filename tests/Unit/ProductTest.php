<?php

namespace Tests\Unit;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Size;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test product creation.
     */
    public function test_can_create_product(): void
    {
        // Create a category and brand for the product
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        // Create a product
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'short_description' => 'This is a test product',
            'description' => 'This is a detailed description of the test product',
            'regular_price' => 100.00,
            'sale_price' => 80.00,
            'SKU' => 'TEST-123',
            'stock_status' => 'instock',
            'featured' => true,
            'quantity' => 10,
            'image' => 'test.jpg',
            'images' => ['test1.jpg', 'test2.jpg'],
        ]);
        
        // Assert the product was created with the correct attributes
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->name);
        $this->assertEquals('test-product', $product->slug);
        $this->assertEquals(100.00, $product->regular_price);
        $this->assertEquals(80.00, $product->sale_price);
        $this->assertEquals('instock', $product->stock_status);
        $this->assertTrue($product->featured);
        $this->assertEquals(10, $product->quantity);
        $this->assertEquals('test.jpg', $product->image);
        $this->assertEquals(['test1.jpg', 'test2.jpg'], $product->images);
        $this->assertEquals($category->id, $product->category_id);
        $this->assertEquals($brand->id, $product->brand_id);
    }
    
    /**
     * Test product relationships.
     */
    public function test_product_relationships(): void
    {
        // Create a category and brand for the product
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();
        
        // Create a product
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
        ]);
        
        // Create reviews for the product
        $reviews = Review::factory()->count(3)->create([
            'product_id' => $product->id,
        ]);
        
        // Create colors for the product
        $colors = Color::factory()->count(2)->create();
        foreach ($colors as $color) {
            $product->colors()->attach($color->id, [
                'quantity' => 5,
                'price_adjustment' => 10.00,
            ]);
        }
        
        // Create sizes for the product
        $sizes = Size::factory()->count(2)->create();
        foreach ($sizes as $size) {
            $product->sizes()->attach($size->id, [
                'quantity' => 5,
                'price_adjustment' => 5.00,
            ]);
        }
        
        // Create related products
        $relatedProducts = Product::factory()->count(2)->create();
        foreach ($relatedProducts as $relatedProduct) {
            $product->relatedProducts()->attach($relatedProduct->id);
        }
        
        // Assert the relationships
        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
        
        $this->assertInstanceOf(Brand::class, $product->brand);
        $this->assertEquals($brand->id, $product->brand->id);
        
        $this->assertCount(3, $product->reviews);
        $this->assertInstanceOf(Review::class, $product->reviews->first());
        
        $this->assertCount(2, $product->colors);
        $this->assertInstanceOf(Color::class, $product->colors->first());
        $this->assertEquals(5, $product->colors->first()->pivot->quantity);
        $this->assertEquals(10.00, $product->colors->first()->pivot->price_adjustment);
        
        $this->assertCount(2, $product->sizes);
        $this->assertInstanceOf(Size::class, $product->sizes->first());
        $this->assertEquals(5, $product->sizes->first()->pivot->quantity);
        $this->assertEquals(5.00, $product->sizes->first()->pivot->price_adjustment);
        
        $this->assertCount(2, $product->relatedProducts);
        $this->assertInstanceOf(Product::class, $product->relatedProducts->first());
    }
    
    /**
     * Test product scopes.
     */
    public function test_product_scopes(): void
    {
        // Create products with different attributes
        $inStockProduct = Product::factory()->create([
            'stock_status' => 'instock',
            'featured' => false,
        ]);
        
        $outOfStockProduct = Product::factory()->create([
            'stock_status' => 'outofstock',
            'featured' => false,
        ]);
        
        $featuredProduct = Product::factory()->create([
            'stock_status' => 'instock',
            'featured' => true,
        ]);
        
        // Test active scope
        $activeProducts = Product::active()->get();
        $this->assertCount(2, $activeProducts);
        $this->assertTrue($activeProducts->contains($inStockProduct));
        $this->assertTrue($activeProducts->contains($featuredProduct));
        $this->assertFalse($activeProducts->contains($outOfStockProduct));
        
        // Test featured scope
        $featuredProducts = Product::featured()->get();
        $this->assertCount(1, $featuredProducts);
        $this->assertTrue($featuredProducts->contains($featuredProduct));
        $this->assertFalse($featuredProducts->contains($inStockProduct));
        $this->assertFalse($featuredProducts->contains($outOfStockProduct));
    }
    
    /**
     * Test product accessors.
     */
    public function test_product_accessors(): void
    {
        // Create a product with regular and sale price
        $product = Product::factory()->create([
            'regular_price' => 100.00,
            'sale_price' => 80.00,
        ]);
        
        // Test current_price accessor
        $this->assertEquals(80.00, $product->current_price);
        
        // Test is_on_sale accessor
        $this->assertTrue($product->is_on_sale);
        
        // Test discount_percentage accessor
        $this->assertEquals(20, $product->discount_percentage);
        
        // Create a product without sale price
        $productNoSale = Product::factory()->create([
            'regular_price' => 100.00,
            'sale_price' => null,
        ]);
        
        // Test current_price accessor with no sale price
        $this->assertEquals(100.00, $productNoSale->current_price);
        
        // Test is_on_sale accessor with no sale price
        $this->assertFalse($productNoSale->is_on_sale);
        
        // Test discount_percentage accessor with no sale price
        $this->assertEquals(0, $productNoSale->discount_percentage);
    }
}
