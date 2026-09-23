<?php

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Color;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Size;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Surfsidemedia\Shoppingcart\Facades\Cart;
use Tests\TestCase;

class ProductVariantSelectionTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Cart::instance('cart')->destroy();

        parent::tearDown();
    }

    public function test_product_color_and_size_are_stored_in_cart_options(): void
    {
        $product = Product::factory()->create([
            'name' => 'Variant Product',
            'regular_price' => 10,
            'sale_price' => null,
            'stock_status' => 'instock',
            'quantity' => 10,
        ]);

        $color = Color::factory()->create([
            'name' => 'Red',
            'code' => 'RED',
            'hex_code' => '#ff0000',
        ]);

        $size = Size::factory()->create([
            'name' => '3-6 أشهر',
            'code' => '3-6M',
        ]);

        $product->colors()->attach($color->id, [
            'quantity' => 10,
            'price_adjustment' => 2,
        ]);

        $product->sizes()->attach($size->id, [
            'quantity' => 10,
            'price_adjustment' => 3.5,
        ]);

        $this->post(route('cart.add'), [
            'id' => $product->id,
            'quantity' => 1,
            'color_id' => $color->id,
            'size_id' => $size->id,
        ])->assertRedirect();

        $item = Cart::instance('cart')->content()->first();

        $this->assertNotNull($item);
        $this->assertSame('15.50', number_format((float) $item->price, 2, '.', ''));
        $this->assertSame('Red', $item->options->color_name);
        $this->assertSame('#ff0000', $item->options->color_hex);
        $this->assertSame('3-6 أشهر', $item->options->size_name);
    }

    public function test_product_with_configured_variants_requires_customer_selection(): void
    {
        $product = Product::factory()->create([
            'regular_price' => 10,
            'sale_price' => null,
            'stock_status' => 'instock',
            'quantity' => 10,
        ]);

        $product->colors()->attach(Color::factory()->create()->id, [
            'quantity' => 10,
            'price_adjustment' => 0,
        ]);

        $this->post(route('cart.add'), [
            'id' => $product->id,
            'quantity' => 1,
        ])
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertTrue(Cart::instance('cart')->content()->isEmpty());
    }

    public function test_selected_product_variants_are_saved_on_order_items(): void
    {
        $user = User::factory()->create();
        Address::create([
            'user_id' => $user->id,
            'name' => 'Customer Name',
            'phone' => '777777777',
            'locality' => 'District',
            'address' => 'Home 1',
            'city' => 'Aden',
            'state' => 'Aden',
            'country' => 'Yemen',
            'landmark' => 'Near school',
            'zip' => '123456',
            'type' => 'home',
            'isdefault' => true,
        ]);

        $product = Product::factory()->create([
            'regular_price' => 20,
            'sale_price' => null,
            'stock_status' => 'instock',
            'quantity' => 10,
        ]);

        $color = Color::factory()->create([
            'name' => 'Blue',
            'code' => 'BLUE',
            'hex_code' => '#0000ff',
        ]);
        $size = Size::factory()->create([
            'name' => '6-12 أشهر',
            'code' => '6-12M',
        ]);

        $product->colors()->attach($color->id, ['quantity' => 10, 'price_adjustment' => 1]);
        $product->sizes()->attach($size->id, ['quantity' => 10, 'price_adjustment' => 0]);

        $this->actingAs($user)->post(route('cart.add'), [
            'id' => $product->id,
            'quantity' => 2,
            'color_id' => $color->id,
            'size_id' => $size->id,
        ])->assertRedirect();

        $this->actingAs($user)->post(route('cart.place.an.order'), [
            'mode' => 'cod',
        ])->assertRedirect(route('cart.order.confirmation'));

        $orderItem = OrderItem::query()->first();

        $this->assertNotNull($orderItem);
        $this->assertSame('Blue', $orderItem->options['color_name']);
        $this->assertSame('#0000ff', $orderItem->options['color_hex']);
        $this->assertSame('6-12 أشهر', $orderItem->options['size_name']);
        $this->assertSame('21.00', (string) $orderItem->price);
    }
}
